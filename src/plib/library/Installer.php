<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

use \PleskX\Api;

class Installer
{
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
        $apiClient = new Api\InternalClient();
        if (!$this->findDnsTemplateRecord($apiClient)) {
            $this->addDnsTemplateRecord($apiClient);
        }
    }

    private function findDnsTemplateRecord(Api\InternalClient $apiClient)
    {
        if (!method_exists($apiClient, 'dnsTemplate')) {
            \pm_Log::debug("DNS Template operator is not supported");
            return null;
        }

        try {
            $dnsRecords = $apiClient->dnsTemplate()->getAll();
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

    /**
     * Add record into DNS template; ignore anny errors occurred
     *
     * @param Api\InternalClient $apiClient
     */
    private function addDnsTemplateRecord(Api\InternalClient $apiClient)
    {
        $operator = method_exists($apiClient, 'dnsTemplate') ? $apiClient->dnsTemplate() : $apiClient->dns();

        try {
            $dnsRecordInfo = $operator->create([
                'type' => 'TXT',
                'host' => "_domainconnect",
                'value' => "domainconnect.plesk.com/host/<hostname>/port/8443",
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
        $apiClient = new Api\InternalClient();
        if ($record = $this->findDnsTemplateRecord($apiClient)) {
            $this->deleteDnsTemplateRecord($apiClient, $record->id);
        }
    }

    private function deleteDnsTemplateRecord(Api\InternalClient $apiClient, $dnsTemplateRecordId)
    {
        if (!method_exists($apiClient, 'dnsTemplate')) {
            \pm_Log::debug("DNS Template operator is not supported");
            return;
        }

        try {
            $apiClient->dnsTemplate()->delete('id', $dnsTemplateRecordId);
        } catch (\Exception $e) {
            \pm_Log::err("Unable to remove record #{$dnsTemplateRecordId} from Plesk DNS template: {$e->getMessage()}");
            \pm_Log::debug($e);
        }
    }
}
