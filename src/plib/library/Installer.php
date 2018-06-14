<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

use \PleskX\Api;

class Installer
{
    private $apiClient;

    public function __construct(Api\Client $apiClient = null)
    {
        $this->apiClient = $apiClient ?: new Api\InternalClient();
    }

    /**
     * Perform module installation activities
     */
    public function install()
    {
        \pm_Log::info("Run installation actions");
        if (\pm_Config::get('dnsProvider')) {
            $this->configureDnsTemplate();
        }
    }

    /**
     * Perform module removing activities
     */
    public function remove()
    {
        \pm_Log::info("Run removing actions");
        $this->cleanUpDnsTemplate();
    }

    /**
     * Add required record into Plesk DNS template
     */
    private function configureDnsTemplate()
    {
        if (!$this->findDnsTemplateRecord()) {
            $this->addDnsTemplateRecord();
        }
    }

    private function findDnsTemplateRecord()
    {
        if (!method_exists($this->apiClient, 'dnsTemplate')) {
            \pm_Log::debug("DNS Template operator is not supported");
            return null;
        }

        try {
            $dnsRecords = $this->apiClient->dnsTemplate()->getAll();
        } catch (\Exception $e) {
            \pm_Log::err($e);
            return null;
        }

        foreach ($dnsRecords as $dnsRecordInfo) {
            if ('TXT' === $dnsRecordInfo->type && '_domainconnect.<domain>.' === $dnsRecordInfo->host) {
                return $dnsRecordInfo;
            }
        }
        return null;
    }

    private function addDnsTemplateRecord()
    {
        $operator = method_exists($this->apiClient, 'dnsTemplate')
            ? $this->apiClient->dnsTemplate()
            : $this->apiClient->dns();

        try {
            $dnsRecordInfo = $operator->create([
                'type' => 'TXT',
                'host' => "_domainconnect",
                'value' => "domainconnect.plesk.com/host/{$this->getPleskHost()}/port/{$this->getPleskPort()}",
            ]);
        } catch (\Exception $e) {
            if (1007 == $e->getCode()) {
                \pm_Log::info("Looks like Domain Connect DNS record already exists in the Plesk DNS template");
            } else {
                \pm_Log::err("Unable to add Domain Connect record into Plesk DNS template");
                \pm_Log::debug($e);
            }
            return;
        }
        $dnsTemplateRecordId = !empty($dnsRecordInfo->id) ? intval($dnsRecordInfo->id) : null;
        if (is_null($dnsTemplateRecordId)) {
            \pm_Log::err("Unable to add Domain Connect TXT record in DNS template, result does not contain the DNS template record identity");
            return;
        }
        \pm_Log::info("Record #{$dnsTemplateRecordId} added into DNS template");
    }

    /**
     * Remove created records from Plesk DNS template
     */
    private function cleanUpDnsTemplate()
    {
        if ($record = $this->findDnsTemplateRecord()) {
            $this->deleteDnsTemplateRecord($record->id);
        }
    }

    private function deleteDnsTemplateRecord($dnsTemplateRecordId)
    {
        if (!method_exists($this->apiClient, 'dnsTemplate')) {
            \pm_Log::debug("DNS Template operator is not supported");
            return;
        }

        try {
            $this->apiClient->dnsTemplate()->delete('id', $dnsTemplateRecordId);
        } catch (\Exception $e) {
            \pm_Log::err("Unable to remove record #{$dnsTemplateRecordId} from Plesk DNS template: {$e->getMessage()}");
            \pm_Log::debug($e);
        }
    }

    public function getPleskHost()
    {
        $hostname = $this->apiClient->server()->getGeneralInfo()->serverName;
        $dot = strrpos($hostname, '.');
        $reservedTLDs = ['.local', '.tld', '.example', '.invalid', '.localhost', '.test'];
        $isValidHostname = !(false === $dot || in_array(substr($hostname, $dot), $reservedTLDs, true));
        if ($isValidHostname && (checkdnsrr($hostname, 'A') || checkdnsrr($hostname, 'AAAA'))) {
            return '<hostname>';
        }
        return '<ip>';
    }

    public function getPleskPort()
    {
        return '8443';
    }
}
