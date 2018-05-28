<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

use \PleskX\Api;

class DomainDns
{
    private $domain;

    public function __construct(\pm_Domain $domain)
    {
        $this->domain = $domain;
    }

    public function addRecord($record)
    {
        \pm_Log::info("Add record for domain #{$this->domain->getId()}");
        $apiClient = new Api\InternalClient();
        $apiClient->dns()->create($this->_getPleskRecordData($this->domain, $record));
    }

    public function removeRecord($record)
    {
        \pm_Log::info("Remove record for domain #{$this->domain->getId()}");
        $apiClient = new Api\InternalClient();
        $apiClient->dns()->delete('id', $record->id);
    }

    public function getRecords()
    {
        \pm_Log::info("Retrieve record for domain #{$this->domain->getId()}");
        $apiClient = new Api\InternalClient();
        $records = [];
        try {
            $dnsRecordsInfo = $apiClient->dns()->getAll('site-id', $this->domain->getId());
            foreach ($dnsRecordsInfo as $dnsRecordInfo) {
                $records[] = (object)array_merge([
                    'id' => $dnsRecordInfo->id,
                    'type' => $dnsRecordInfo->type,
                ], $this->_getDomainConnectRecordData(
                    $this->domain->getName(),
                    $dnsRecordInfo->type,
                    $dnsRecordInfo->host,
                    $dnsRecordInfo->value,
                    $dnsRecordInfo->opt
                ));
            }
        } catch (\Exception $e) {
            \pm_Log::err("Unable to retrieve DNS records for domain '{$this->domain->getDisplayName()}'");
            \pm_Log::debug($e);
            return [];
        }
        return $records;
    }

    /**
     * Return RNS record data in Plesk format
     *
     * @param \pm_Domain $domain
     * @param \stdClass $record
     * @return array
     */
    protected function _getPleskRecordData(\pm_Domain $domain, \stdClass $record)
    {
        if ('@' === $record->host) {
            $host = '';
        } else {
            $host = $record->host;
        }
        $value = '';
        $opt = '';
        switch ($record->type) {
            case 'A':
            case 'AAAA':
                $value = $record->pointsTo;
                break;
            case 'CNAME':
            case 'NS':
                $value = '@' === $record->pointsTo ? $domain->getName() : $record->pointsTo;
                break;
            case 'MX':
                $value = $this->_getRecordValueByKeys($record, ['pointsTo', 'target']);
                $opt = $record->priority;
                break;
            case 'TXT':
                $value = $this->_getRecordValueByKeys($record, ['data', 'target']);
                break;
            case 'SRV':
                $host = $this->_getRecordValueByKeys($record, ['name', 'host']);
                $host = '@' === $host ? '' : $host;
                $host = rtrim("{$record->service}.{$record->protocol}.{$host}", '.');
                $value = $this->_getRecordValueByKeys($record, ['pointsTo', 'target']);
                $opt = "{$record->priority} {$record->weight} {$record->port}";
                break;
        }
        return [
            'site-id' => $domain->getId(),
            'type' => $record->type,
            'host' => $host,
            'value' => $value,
            'opt' => $opt,
        ];
    }

    protected function _getRecordValueByKeys(\stdClass $record, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($record->$key)) {
                return $record->$key;
            }
        }
        return '';
    }

    /**
     * Return DNS record data in Domain Connect format
     *
     * @param string $type
     * @param string$host
     * @param string $value
     * @param $opt
     * @param string $domainName
     * @return array
     */
    protected function _getDomainConnectRecordData($domainName, $type, $host, $value, $opt)
    {
        switch ($type) {
            case 'A':
            case 'AAAA':
                return [
                    'host' => $this->_convertHost($domainName, $host),
                    'pointsTo' => $value,
                ];
            case 'CNAME':
            case 'NS':
                return [
                    'host' => $this->_convertHost($domainName, $host),
                    'pointsTo' => $this->_convertHost($domainName, $value),
                ];
            case 'MX':
                return [
                    'host' => $this->_convertHost($domainName, $host),
                    'pointsTo' => $value,
                    'priority' => (string)(int)$opt,
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
                    'service' => isset($hostData[0]) ? $hostData[0] : '',
                    'protocol' => isset($hostData[1]) ? $hostData[1] : '',
                    'name' => $this->_convertHost($domainName, $serviceHost),
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
