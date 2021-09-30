<?php
// Copyright 1999-2019. Plesk International GmbH.

namespace PleskExt\DomainConnect;

use \PleskX\Api;

class DomainConnect
{
    private $domain;
    private $urlPrefix;
    private $data;
    private $apiClient;
    private $serviceId;

    public function __construct(\pm_Domain $domain, $serviceId = 'web')
    {
        $this->domain = $domain;
        $this->apiClient = new Api\InternalClient();
        $this->serviceId = $serviceId;
    }

    private function getData()
    {
        if (null === $this->urlPrefix) {
            $this->urlPrefix = $this->fetchUrlPrefix($this->getRootDomainName());
            \pm_Log::debug("TXT _domainconnect: {$this->urlPrefix}");
        }
        if (null === $this->data) {
            $url = "https://{$this->urlPrefix}/v2/{$this->domain->getName()}/settings";
            \pm_Log::debug("DomainConnect request: GET {$url}");
            try {
                $client = new \Zend_Http_Client($url);
                $response = $client->request(\Zend_Http_Client::GET);
            } catch (\Zend_Uri_Exception $e) {
                throw new \pm_Exception("Cannot fetch DomainConnect data: {$e->getMessage()}");
            }
            if ($response->getStatus() !== 200) {
                throw new \pm_Exception("Cannot fetch DomainConnect data {$response->getStatus()}: {$response->getMessage()}");
            }
            \pm_Log::debug("DomainConnect response {$response->getStatus()}: {$response->getBody()}");
            $this->data = json_decode($response->getBody());
        }
        return $this->data;
    }

    private function fetchUrlPrefix($domainName)
    {
        $dnsRecords = Dns::txtRecords("_domainconnect.{$domainName}");
        foreach ($dnsRecords as $record) {
            try {
                \Zend_Uri_Http::fromString("https://{$record}");
            } catch(\Zend_Uri_Exception $e) {
                // not valid URL
                continue;
            }
            return $record;
        }
        throw new \pm_Exception("Could not find domain connect URL prefix for {$domainName}.");
    }

    public function getSyncUx()
    {
        return rtrim($this->getData()->urlSyncUX, '/');
    }

    public function getApplyTemplateUrl($serviceId, array $properties)
    {
        $providerId = \pm_Config::get('providerId');

        $properties = array_merge([
            'domain' => $this->getRootDomainName(),
            'providerName' => \pm_Config::get('providerName'),
        ], $properties);

        $properties = http_build_query($properties);

        return "{$this->getSyncUx()}/v2/domainTemplates/providers/{$providerId}/services/{$serviceId}/apply?{$properties}";
    }

    public function disable()
    {
        $this->domain->setSetting('enabled', 0);
    }

    public function isEnabled($templateId = '')
    {
        return (int) $this->domain->getSetting($templateId . 'enabled') > 0;
    }

    public function isConnected($templateId = '')
    {
        return (int) $this->domain->getSetting($templateId . 'connected') > 0;
    }

    public function isConnectable($templateId = '')
    {
        return (int) $this->domain->getSetting($templateId . 'connectable') > 0;
    }

    public function getWindowOptions()
    {
        return [
            'width' => (int) $this->domain->getSetting('windowOptionWidth', 750),
            'height' => (int) $this->domain->getSetting('windowOptionHeight', 750),
        ];
    }

    public function getConfigureUrl($templateId = '')
    {
        return $this->domain->getSetting($templateId . 'configureUrl');
    }

    public function mailServiceEnabled()
    {
        try {
            $domain_id = $this->domain->getId();
            $xml = $this->apiClient->Mail()->request("<mail><get_prefs><filter><site-id>" . $domain_id . "</site-id></filter></get_prefs></mail>");
            return $xml->prefs->mailservice == 'true';
        } catch (\Exception $e) {
            \pm_Log::debug($e->getMessage());
        }
        return false;
    }

    public function webServiceEnabled()
    {
        return $this->domain->hasHosting();
    }

    /**
     * @return array
     */
    private function getRecordsForWeb()
    {
        //start configure A records for web service
        $arrayToReturn = [];
        $groupIdWeb = \pm_Config::get('webServiceGroupId');
        $hostingIps = $this->domain->getIpAddresses();
        try {
            $resolvedIp = Dns::aRecord($this->domain->getName());
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
            return $arrayToReturn;
        }

        if (in_array($resolvedIp, $hostingIps)) {
            return $arrayToReturn;
        } else {
            return [$groupIdWeb => ['ip' => $resolvedIp]];
        }
    }

    /**
     * @return array
     */
    private function getRecordsForMail()
    {
        $arrayToReturn = [];

        //start configure MX records for mail service
        try {
            $resolvedIp = Dns::mxRecord($this->domain->getName());
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
            $resolvedIp = ['ip' => ''];
        }
        $hostingIps = $this->domain->getIpAddresses();
        if (in_array($resolvedIp['ip'], $hostingIps)) {
            \pm_Log::info("Domain {$this->domain->getDisplayName()} is already resolved to the current server");
            $this->domain->setSetting('connected', 1);
            return $arrayToReturn;
        }

        //start configure SPFM records for mail service
        $hostname = $this->apiClient->server()->getGeneralInfo()->serverName;
        $arrayToReturn = ['ip' => reset($hostingIps), 'spfrules' => 'include:' . $hostname];

        return $arrayToReturn;
    }

    public function init()
    {
        if (!$this->domain->hasHosting()) {
            return;
        }

        $this->domain->setSetting('enabled', 1);
        $this->domain->setSetting('connected', 0);
        $this->domain->setSetting('connectable', 0);
        $this->domain->setSetting('configureUrl', '');
        $this->domain->setSetting('configureLinkClicked', 0);

        try {
            $resolvedIp = Dns::aRecord($this->domain->getName());
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
            $resolvedIp = '';
        }

        $hostingIps = $this->domain->getIpAddresses();

        if (in_array($resolvedIp, $hostingIps)) {
            \pm_Log::info("Domain {$this->domain->getDisplayName()} is already resolved to the current server");
            $this->domain->setSetting('connected', 1);
            return;
        }

        \pm_Log::debug("Domain {$this->domain->getDisplayName()} is resolved to {$resolvedIp}, but expected " . join(' or ', $hostingIps));

        $properties = ['ip' => reset($hostingIps)];
        if ($this->domainHasParent()) {
            $properties['host'] = DomainDns::relativeHost($this->getRootDomainName(), $this->domain->getName());
            $properties['groupId'] = \pm_Config::get('webServiceGroupId');
        }

        $url = $this->getApplyTemplateUrl($this->serviceId, $properties);

        $this->domain->setSetting('connectable', 1);
        $this->domain->setSetting('configureUrl', $url);

        if (isset($this->getData()->width, $this->getData()->height)) {
            $this->domain->setSetting('windowOptionWidth', $this->getData()->width);
            $this->domain->setSetting('windowOptionHeight', $this->getData()->height);
        }
    }


    public function initService()
    {
        $isenabled = $this->serviceId . 'ServiceEnabled';
        if (!$this->$isenabled()) {
            return;
        }

        $this->domain->setSetting($this->serviceId . 'enabled', 1);
        $this->domain->setSetting($this->serviceId . 'connected', 0);
        $this->domain->setSetting($this->serviceId . 'connectable', 0);
        $this->domain->setSetting($this->serviceId . 'configureUrl', '');
        $this->domain->setSetting($this->serviceId . 'configureLinkClicked', 0);


        $getRecords = 'getRecordsFor' . ucfirst($this->serviceId);
        $recordsArray = $this->$getRecords();
        if (count($recordsArray) == 0) {
            return;
        }

        $hostingIps = $this->domain->getIpAddresses();
        \pm_Log::info("Domain {$this->domain->getDisplayName()} service {$this->serviceId} is resolved somewhere else but expected " .
            join(' or ', $hostingIps));
        \pm_Log::debug("Got the following result to process service {$this->serviceId}: " . print_r($recordsArray, true));

        $url = $this->getApplyTemplateUrl($this->serviceId, $recordsArray);

        $this->domain->setSetting($this->serviceId . 'connectable', 1);
        $this->domain->setSetting($this->serviceId . 'configureUrl', $url);

        if (isset($this->getData()->width, $this->getData()->height)) {
            $this->domain->setSetting('windowOptionWidth', $this->getData()->width);
            $this->domain->setSetting('windowOptionHeight', $this->getData()->height);
        }
    }

    /**
     * @throws \pm_Exception
     */
    private function getRootDomainName()
    {
        $parentDomainId = (int)$this->domain->getProperty('parentDomainId');
        $rootDomain = $parentDomainId === 0 ? $this->domain : \pm_Domain::getByDomainId($parentDomainId);

        return $rootDomain->getName();
    }

    private function domainHasParent()
    {
        return (int)$this->domain->getProperty('parentDomainId') !== 0;
    }
}
