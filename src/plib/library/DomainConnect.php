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
            } catch (\Zend_Http_Client_Exception $e) {
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
            $xml = $this->apiClient->Mail()->request("<get_prefs><filter><site-id>" . $domain_id . "</site-id></filter></get_prefs>");
            return $xml->mail->get_prefs->result->prefs->mailservice == 'true';
        } catch (\Exception $e) {
            \pm_Log::info($e);
        }
        return false;
    }

    public function webServiceEnabled()
    {
        try {
            return $this->domain->hasHosting();
        } catch (Exception $e) {
            \pm_Log::info($e);
        }
        return false;
    }

    /**
     * @return object|null
     */
    private function getRecordsForWeb()
    {
        //start configure A records for web service
        $hostingIps = $this->domain->getIpAddresses();
        try {
            $resolvedIp = Dns::aRecord($this->domain->getName());
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
            return null;
        }

        if (in_array($resolvedIp, $hostingIps)) {
            return null;
        } else {
            return (object)['resolvedIp' => $resolvedIp, 'hostingIps' => $hostingIps];
        }
    }

    /**
     * @param array $allRecords
     * @param string $hostname
     * @return array
     */
    private function getSpfRecords(array $allRecords, $hostname)
    {
        $spfRecords = [];
        foreach ($allRecords as $record) {
            if ($record->type == 'TXT' && preg_match("/^v=spf1 .*\+mx.+\:" . $hostname . ".+all$/", $record->data) == 1)
                $spfRecords[] = $record;
            if ($record->type == 'SPFM')
                $spfRecords[] = $record;
        }
        return $spfRecords;
    }

    /**
     * @param array $allRecords
     * @return object|null
     */
    private function getRecordsForMail(array $allRecords)
    {
        $objectToReturn = null;
        //start configure MX and SPF records for mail service
        try {
            $resolvedIp = Dns::mxRecord($this->domain->getName());
            $mxRecord = reset(array_filter($allRecords, function ($record) {return $record->type == 'MX'; }));
            $mxRecordA = reset(array_filter($allRecords, function ($record) use ($mxRecord) { return $record->type == 'A' && $record->host == $mxRecord->pointsTo; }));
            if ($resolvedIp['ip'] == $mxRecordA->pointsTo) {
                \pm_Log::info("MX record for domain {$this->domain->getDisplayName()} is already resolved to the current server");
            }
            $objectToReturn = (object)['resolvedIp' => $resolvedIp, 'host' => $mxRecord->pointsTo, 'priority' => $mxRecord->priority];
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
            return $objectToReturn;
        }
        //start configure SPF records for mail service
        try {
            $hostname = $this->apiClient->server()->getGeneralInfo()->serverName;
            $spfRecords = $this->getSpfRecords($allRecords, $hostname);
            $objectToReturn->spfRecords = $spfRecords;
        } catch (\Exception $e) {
            \pm_Log::warn($e);
        }
        //start configure A records of webmail.% for mail service
        try {
            $webmailIpRecord = reset(
                array_filter($allRecords,
                    function ($record){
                        return $record->type == 'A' && $record->host == "webmail." . $this->domain->getName();
                    }
                )
            );
            $objectToReturn->webmailIpRecord = $webmailIpRecord;
        } catch (\Exception $e) {
            \pm_Log::warn($e);
        }

        return $objectToReturn;
    }

    public function init()
    {
        if (!$this->webServiceEnabled() && !$this->mailServiceEnabled()) {
            return;
        }

        $this->domain->setSetting('enabled', 1);
        $this->domain->setSetting('connected', 0);
        $this->domain->setSetting('connectable', 0);
        $this->domain->setSetting('configureUrl', '');
        $this->domain->setSetting('mailConnected', 0);
        $this->domain->setSetting('mailConnectable', 0);
        $this->domain->setSetting('mailConfigureUrl', '');
        $this->domain->setSetting('configureLinkClicked', 0);

        $groupIdMail = \pm_Config::get('mailServiceGroupId');
        $groupIdWeb = \pm_Config::get('webServiceGroupId');
        $groupIdWebmail = \pm_Config::get('webMailServiceGroupId');
        $groupIdSpf = \pm_Config::get('spfServiceGroupId');

        $groupId = [];
        $allRecords = array_map(
            function ($record) {
                $record->host = rtrim($record->host, '.');
                $record->pointsTo = rtrim($record->pointsTo, '.');
                return $record;
            },
            (new DomainDns($this->domain))->getRecords()
        );


        $webRecordsObject = $this->getRecordsForWeb();
        $mailRecordsObject = $this->getRecordsForMail($allRecords);
        if (is_null($webRecordsObject) && is_null($mailRecordsObject)) {
            \pm_Log::info("Domain {$this->domain->getDisplayName()} is already resolved to the current server");
            $this->domain->setSetting('connected', 1);
            return;
        } elseif (!is_null($webRecordsObject) && $webRecordsObject->resolvedIp != '') {
            \pm_Log::debug("Domain {$this->domain->getDisplayName()} is resolved to {$webRecordsObject->resolvedIp}, but expected " .
                join(' or ', $webRecordsObject->hostingIps));
            $groupId[] = $groupIdWeb;
        } elseif (!is_null($mailRecordsObject) && $mailRecordsObject->resolvedIp != '') {
            \pm_Log::info("MX record for domain {$this->domain->getDisplayName()} is resolved to {$mailRecordsObject->resolvedIp}, but expected " .
                join(' or ', $mailRecordsObject->hostingIps));
        }


        $templates = [];
        if (in_array($groupIdWeb, $groupId)) {
            $templates['ip'] = reset($hostingIps);
        }
        if (in_array($groupIdMail, $groupId)) {
            $templates['mxhost'] = $mxResolvedIp['host']; $templates['mxip'] = $mxResolvedIp['ip']; $templates['mxpriority'] = $mxResolvedIp['priority'];
        }
        if (in_array($groupIdWebmail, $groupId)) {
            $templates['webmailip'] = $webmailIpRecord->pointsTo;
        }
        if (in_array($groupIdSpf, $groupId)) {
            $templates['spftxt'] = reset($spfRecords)->data;
        }
        $templates['groupId'] = implode(",", $groupId);

        try {
            $url = $this->getApplyTemplateUrl($this->serviceId, $templates);
        } catch (\Exception $e) {
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

    public function initMail()
    {
        if (!$this->webServiceEnabled() && !$this->mailServiceEnabled()) {
            return;
        }

        $this->domain->setSetting('enabled', 1);
        $this->domain->setSetting('connected', 0);
        $this->domain->setSetting('connectable', 0);
        $this->domain->setSetting('configureUrl', '');
        $this->domain->setSetting('mailConnected', 0);
        $this->domain->setSetting('mailConnectable', 0);
        $this->domain->setSetting('mailConfigureUrl', '');
        $this->domain->setSetting('configureLinkClicked', 0);

        $groupIdMail = \pm_Config::get('mailServiceGroupId');
        $groupIdWeb = \pm_Config::get('webServiceGroupId');
        $groupIdWebmail = \pm_Config::get('webMailServiceGroupId');
        $groupIdSpf = \pm_Config::get('spfServiceGroupId');

        $groupId = [];
        $allRecords = array_map(
            function ($record) {
                $record->host = rtrim($record->host, '.');
                $record->pointsTo = rtrim($record->pointsTo, '.');
                return $record;
            },
            (new DomainDns($this->domain))->getRecords()
        );


        $webRecordsObject = $this->getRecordsForWeb();
        $mailRecordsObject = $this->getRecordsForMail($allRecords);
        if (is_null($webRecordsObject) && is_null($mailRecordsObject)) {
            \pm_Log::info("Domain {$this->domain->getDisplayName()} is already resolved to the current server");
            $this->domain->setSetting('connected', 1);
            return;
        } elseif (!is_null($webRecordsObject) && $webRecordsObject->resolvedIp != '') {
            \pm_Log::debug("Domain {$this->domain->getDisplayName()} is resolved to {$webRecordsObject->resolvedIp}, but expected " .
                join(' or ', $webRecordsObject->hostingIps));
            $groupId[] = $groupIdWeb;
        } elseif (!is_null($mailRecordsObject) && $mailRecordsObject->resolvedIp != '') {
            \pm_Log::info("MX record for domain {$this->domain->getDisplayName()} is resolved to {$mailRecordsObject->resolvedIp}, but expected " .
                join(' or ', $mailRecordsObject->hostingIps));
        }


        $templates = [];
        if (in_array($groupIdWeb, $groupId)) {
            $templates['ip'] = reset($hostingIps);
        }
        if (in_array($groupIdMail, $groupId)) {
            $templates['mxhost'] = $mxResolvedIp['host']; $templates['mxip'] = $mxResolvedIp['ip']; $templates['mxpriority'] = $mxResolvedIp['priority'];
        }
        if (in_array($groupIdWebmail, $groupId)) {
            $templates['webmailip'] = $webmailIpRecord->pointsTo;
        }
        if (in_array($groupIdSpf, $groupId)) {
            $templates['spftxt'] = reset($spfRecords)->data;
        }
        $templates['groupId'] = implode(",", $groupId);

        try {
            $url = $this->getApplyTemplateUrl($this->serviceId, $templates);
        } catch (\Exception $e) {
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

    private function domainHasParent()
    {
        return (int)$this->domain->getProperty('parentDomainId') !== 0;
    }
}
