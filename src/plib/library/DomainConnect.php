<?php
// Copyright 1999-2018. Plesk International GmbH.

namespace PleskExt\DomainConnect;

use \PleskX\Api;

class DomainConnect
{
    private $domain;
    private $urlPrefix;
    private $data;
    private $apiClient;

    public function __construct(\pm_Domain $domain)
    {
        $this->domain = $domain;
    }

    private function getData()
    {
        if (null === $this->urlPrefix) {
            $this->urlPrefix = $this->fetchUrlPrefix($this->domain->getName());
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

//        \pm_Log::warn(print_r($properties, true));
        $properties = array_merge([
            'domain' => $this->domain->getName(),
            'providerName' => \pm_Config::get('providerName'),
        ], $properties);
        \pm_Log::warn(print_r($properties, true));

        $properties = http_build_query($properties);

//        \pm_Log::warn(print_r($properties, true));

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

    public function mailServiceEnabled()
    {
        try {
            $domain_id = $this->domain->getId();
            $this->apiClient = new Api\InternalClient();
            $xml = $this->apiClient->Mail()->request("<get_prefs><filter><site-id>" . $domain_id . "</site-id></filter></get_prefs>");
//            $xml = \pm_ApiRpc::getService()->call("<packet><mail><get_prefs><filter><site-id>" . $domain_id . "</site-id></filter></get_prefs></mail></packet>");
            \pm_Log::info($xml);
            return $xml->mail->get_prefs->result->prefs->mailservice == 'true';
        } catch (Exception $e) {
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

    public function init()
    {
        if (!$this->webServiceEnabled() && !$this->mailServiceEnabled()) {
            return;
        }

        $this->domain->setSetting('enabled', 1);
        $this->domain->setSetting('connected', 0);
        $this->domain->setSetting('connectable', 0);
        $this->domain->setSetting('configureUrl', '');
        $this->domain->setSetting('configureLinkClicked', 0);

        $serviceId = \pm_Config::get('ServiceId');
        $groupIdMail = \pm_Config::get('mailServiceGroupId');
        $groupIdWeb = \pm_Config::get('webServiceGroupId');
        $groupIdWebmail = \pm_Config::get('webMailServiceGroupId');
        $groupIdSpf = \pm_Config::get('spfServiceGroupId');
        $hostingIps = $this->domain->getIpAddresses();
        $groupId = [];
        \pm_Log::warn("msavrilov");
        $allRecords = array_map(
            function ($record) {
                $record->host = rtrim($record->host, '.');
                $record->pointsTo = rtrim($record->pointsTo, '.');
                return $record;
            },
            (new DomainDns($this->domain))->getRecords()
        );
//        foreach ($allRecords as $record) {
//            $record->host = rtrim($record->host, '.');
//            $record->pointsTo = rtrim($record->pointsTo, '.');
//        }
        \pm_Log::warn(print_r($allRecords, true));

//start configure A records for web service
        try {
            $resolvedIp = Dns::aRecord($this->domain->getName());
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);

            $resolvedIp = '';
        }

        if (in_array($resolvedIp, $hostingIps)) {
            \pm_Log::info("Domain {$this->domain->getDisplayName()} is already resolved to the current server");

            $this->domain->setSetting('connected', 1);

        } else {
            $groupId[] = $groupIdWeb;
        }

        \pm_Log::debug("Domain {$this->domain->getDisplayName()} is resolved to {$resolvedIp}, but expected " . join(' or ', $hostingIps));

//start configure MX and SPF records for mail service
        try {
            $resolvedIp = Dns::mxRecord($this->domain->getName());
            $mxRecord = reset(array_filter($allRecords, function ($record) { return $record->type == 'MX'; }));
            $mxRecordA = reset(array_filter($allRecords, function ($record) use ($mxRecord) { return $record->type == 'A' && $record->host == $mxRecord->pointsTo; }));
            if ($resolvedIp['ip'] == $mxRecordA->pointsTo) {
                \pm_Log::info("MX record for domain {$this->domain->getDisplayName()} is already resolved to the current server");
                if ($this->domain->getSetting('connected', 1)) {
                    return;
                }
            } else {
                if ($this->domain->getSetting('connected', 1)) {
                    $this->domain->setSetting('connected', 0);
                }
            }
            $mxResolvedIp = ['ip' => $mxRecordA->pointsTo, 'host' => $mxRecord->pointsTo, 'priority' => $mxRecord->priority];
            $groupId[] = $groupIdMail;
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
        }

        try {
            $hostname = gethostbyaddr(gethostbyname(gethostname()));
            \pm_Log::warn($hostname);
            $spfRecords = array_filter($allRecords, function ($record) use ($hostname) {
                return $record->type == 'TXT' && preg_match("/^v=spf1 .*\+mx.+\:" . $hostname . ".+all$/", $record->data) == 1;
            });
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
            $spfRecords = [];
        }
        if (sizeof($spfRecords) > 0) {
            $groupId[] = $groupIdSpf;
        }

        //start configure A records of webmail.% for mail service
        try {
            $webmailIpRecord = reset(array_filter($allRecords, function ($record) { return $record->type == 'A' && $record->host == "webmail." . $this->domain->getName(); }));
            $groupId[] = $groupIdWebmail;
            \pm_Log::warn(print_r($webmailIpRecord, true));
        } catch (\pm_Exception $e) {
            \pm_Log::warn($e);
        }


        $templates = [];
        if (in_array($groupIdWeb, $groupId)) {$templates['ip'] = reset($hostingIps);}
        if (in_array($groupIdMail, $groupId)) {$templates['mxhost'] = $mxResolvedIp['host']; $templates['mxip'] = $mxResolvedIp['ip']; $templates['mxpriority'] = $mxResolvedIp['priority'];}
        if (in_array($groupIdWebmail, $groupId)) {$templates['webmailip'] = $webmailIpRecord->pointsTo;}
        if (in_array($groupIdSpf, $groupId)) {$templates['spftxt'] = reset($spfRecords)->data;}
        $templates['groupId'] = implode(",", $groupId);
//        \pm_Log::warn(print_r($templates, true));

        try {
            $url = $this->getApplyTemplateUrl($serviceId, $templates);
        } catch (\pm_Exception $e) {
            \pm_Log::info($e->getMessage());

            return;
        }
        \pm_Log::warn(print_r($url, true));

        $this->domain->setSetting('connectable', 1);
        $this->domain->setSetting('configureUrl', $url);


        try {
            if (isset($this->getData()->width, $this->getData()->height)) {
                $this->domain->setSetting('windowOptionWidth', $this->getData()->width);
                $this->domain->setSetting('windowOptionHeight', $this->getData()->height);
            }
        } catch (\pm_Exception $e) {
            $this->domain->setSetting('windowOptionWidth', 400);
            $this->domain->setSetting('windowOptionHeight', 300);
            \pm_Log::info($e->getMessage());
        }
    }
}
