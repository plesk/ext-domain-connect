<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.
namespace PleskExt\DomainConnect;

use \PleskX\Api;

class DomainDns
{
    /**
     * Return list of Plesk DNS record for given domain
     *
     * @param \pm_Domain $domain
     * @return array
     */
    public function getRecords(\pm_Domain $domain)
    {
        $apiClient = new Api\InternalClient();
        $records = [];
        try {
            $dnsRecordsInfo = $apiClient->dns()->getAll('site-id', $domain->getId());
            foreach ($dnsRecordsInfo as $dnsRecordInfo) {
                $records[] = (object)array_merge([
                    'type' => $dnsRecordInfo->type,
                    'host' => $this->_convertHost($dnsRecordInfo->host, $domain->getName()),
                ], $this->_getAdditionalData(
                    $dnsRecordInfo->type,
                    $dnsRecordInfo->value,
                    $dnsRecordInfo->opt)
                );
            }
        } catch (\Exception $e) {
            \pm_Log::err("Unable to retrieve DNS records for domain '{$domain->getDisplayName()}'");
            \pm_Log::debug($e);
            return [];
        }
        return $records;
    }

    /**
     * Return host converted to the format compatible with Domain Connect templates
     *
     * @param $rawHost
     * @param $domainName
     * @return string
     */
    protected function _convertHost($rawHost, $domainName)
    {
        $host = $rawHost;
        $pos = strrpos($rawHost, $domainName);
        if (0 === $pos) {
            $host = '@';
        } else if (false !== $pos) {
            $host = substr($host, 0, $pos - 1);
        }
        return $host;
    }

    /**
     * @param string $type
     * @param string $value
     * @param $opt
     * @return array
     */
    protected function _getAdditionalData($type, $value, $opt)
    {
        switch ($type) {
            case 'A':
                return [
                    'pointsTo' => $value
                ];
            default:
                return [
                    'value' => $value,
                    'opt' => $opt,
                ];
        }
    }
}
