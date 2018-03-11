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
                ], $this->_getData(
                    $domain->getName(),
                    $dnsRecordInfo->type,
                    $dnsRecordInfo->host,
                    $dnsRecordInfo->value,
                    $dnsRecordInfo->opt
                ));
            }
        } catch (\Exception $e) {
            \pm_Log::err("Unable to retrieve DNS records for domain '{$domain->getDisplayName()}'");
            \pm_Log::debug($e);
            return [];
        }
        return $records;
    }

    /**
     * @param string $type
     * @param string$host
     * @param string $value
     * @param $opt
     * @param string $domainName
     * @return array
     */
    protected function _getData($domainName, $type, $host, $value, $opt)
    {
        switch ($type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
            case 'NS':
                return [
                    'host' => $this->_convertHost($domainName, $host),
                    'pointsTo' => $value,
                ];
            case 'MX':
                return [
                    'host' => $this->_convertHost($domainName, $host),
                    'pointsTo' => $value,
                    'priority' => $opt,
                    'target' => $value,
                ];
            case 'TXT':
                return [
                    'host' => $this->_convertHost($domainName, $host),
                    'data' => $value,
                    'target' => $value,
                ];
            case 'SRV':
                $hostData = explode('.', $host);
                $optData = explode(' ', $opt);
                $serviceHost = implode('.' , array_slice($hostData, 2));
                return [
                    'name' => isset($hostData[0]) ? $hostData[0] : '',
                    'service' => isset($hostData[0]) ? $hostData[0] : '',
                    'protocol' => isset($hostData[1]) ? $hostData[1] : '',
                    'host' => $this->_convertHost($domainName, $serviceHost),
                    'priority' => isset($optData[0]) ? $optData[0] : '',
                    'weight' => isset($optData[1]) ? $optData[1] : '',
                    'port' => isset($optData[2]) ? $optData[2] : '',
                    'pointsTo' => $value,
                    'target' => $value,
                ];
            default:
                return [
                    'host' => $host,
                    'value' => $value,
                    'opt' => $opt,
                ];
        }
    }

    /**
     * Return host converted to the format compatible with Domain Connect templates
     *
     * @param $domainName
     * @param $rawHost
     * @return string
     */
    protected function _convertHost($domainName, $rawHost)
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
}
