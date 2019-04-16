<?php
// Copyright 1999-2018. Plesk International GmbH.

namespace PleskExt\DomainConnect;

class DomainConnect
{
    private $domain;
    private $urlPrefix;
    private $data;

    public function __construct(\pm_Domain $domain)
    {
        $this->domain = $domain;
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
            'domain' => $this->domain->getName(),
            'providerName' => \pm_Config::get('providerName'),
        ], $properties);

        $properties = http_build_query($properties);

        return "{$this->getSyncUx()}/v2/domainTemplates/providers/{$providerId}/services/{$serviceId}/apply?{$properties}";
    }

    public function disable()
    {
        $this->domain->setSetting('enabled', 0);
    }

    public function isEnabled()
    {
        return (int) $this->domain->getSetting('enabled') > 0;
    }

    public function isConnected()
    {
        return (int) $this->domain->getSetting('connected') > 0;
    }

    public function isConnectable()
    {
        return (int) $this->domain->getSetting('connectable') > 0;
    }

    public function getWindowOptions()
    {
        return [
            'width' => (int) $this->domain->getSetting('windowOptionWidth', 750),
            'height' => (int) $this->domain->getSetting('windowOptionHeight', 750),
        ];
    }

    public function getConfigureUrl()
    {
        return $this->domain->getSetting('configureUrl');
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

        $serviceId = \pm_Config::get('webServiceId');

        try {
            $url = $this->getApplyTemplateUrl($serviceId, [
                'ip' => reset($hostingIps),
            ]);
        } catch (\pm_Exception $e) {
            \pm_Log::info($e->getMessage());

            return;
        }

        $this->domain->setSetting('connectable', 1);
        $this->domain->setSetting('configureUrl', $url);

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
}
