<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

use \PleskX\Api;

class Installer
{
    const DNS_TEMPLATE_RECORD_ID = 'dns-template-record-id';

    /**
     * Perform module installation activities
     */
    public function install()
    {
        \pm_Log::info("Run installation actions");
        $this->_configureDnsTemplate();
    }

    /**
     * Perform module removing activities
     */
    public function remove()
    {
        \pm_Log::info("Run removing actions");
        $this->_cleanUpDnsTemplate();
    }

    /**
     * Add required record into Plesk DNS template
     */
    protected function _configureDnsTemplate()
    {
        $apiClient = new Api\InternalClient();
        $this->_addDnsTemplateRecord($apiClient);
    }

    /**
     * Add record into DNS template; ignore anny errors occurred
     *
     * @param Api\InternalClient $apiClient
     */
    protected function _addDnsTemplateRecord(Api\InternalClient $apiClient)
    {
        try {
            $dnsRecordInfo = $apiClient->dns()->create([
                'type' => 'TXT',
                'host' => "_domainconnect",
                'value' => "domainconnect.plesk.space/host/<hostname>/port/8443",
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
        \pm_Settings::set(static::DNS_TEMPLATE_RECORD_ID, $dnsTemplateRecordId);
    }

    /**
     * Remove created records from Plesk DNS template
     */
    protected function _cleanUpDnsTemplate()
    {
        $dnsTemplateRecordId = \pm_Settings::get(static::DNS_TEMPLATE_RECORD_ID);
        if (is_null($dnsTemplateRecordId)) {
            return;
        }
        $apiClient = new Api\InternalClient();
        try {
            $apiClient->dns()->delete('id', $dnsTemplateRecordId);
        } catch (\Exception $e) {
            \pm_Log::err("Unable to remove record #{$dnsTemplateRecordId} from Plesk DNS template: {$e->getMessage()}");
            \pm_Log::debug($e);
        }
    }
}
