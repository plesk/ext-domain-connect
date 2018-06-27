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
        $apiClient->dns()->create($this->formatRecordForAPI($this->domain, $record));
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
                ], $this->formatRecordForDomainConnect(
                    $this->domain,
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

    public function formatRecordForAPI(\pm_Domain $domain, \stdClass $record)
    {
        switch ($record->type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
            case 'NS':
                return [
                    'site-id' => $domain->getId(),
                    'type' => $record->type,
                    'host' => $this->relativeHost($domain, $record->host),
                    'value' => $record->pointsTo,
                    'opt' => '',
                ];
            case 'MX':
                return [
                    'site-id' => $domain->getId(),
                    'type' => $record->type,
                    'host' => $this->relativeHost($domain, $record->host),
                    'value' => $record->pointsTo,
                    'opt' => $record->priority,
                ];
            case 'TXT':
                return [
                    'site-id' => $domain->getId(),
                    'type' => $record->type,
                    'host' => $this->relativeHost($domain, $record->host),
                    'value' => $record->data,
                    'opt' => '',
                ];
            case 'SRV':
                return [
                    'site-id' => $domain->getId(),
                    'type' => $record->type,
                    'host' => $this->relativeHost($domain, "{$record->service}.{$record->protocol}.{$record->name}"),
                    'value' => $record->target,
                    'opt' => "{$record->priority} {$record->weight} {$record->port}",
                ];
            default:
                throw new Exception\RecordNotSupported("Record type '{$record->type}' is not supported");
        }
    }

    public function formatRecordForDomainConnect(\pm_Domain $domain, $type, $host, $value, $opt)
    {
        switch ($type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
            case 'NS':
                return [
                    'host' => $this->fullHost($domain, $host),
                    'pointsTo' => $value,
                ];
            case 'MX':
                return [
                    'host' => $this->fullHost($domain, $host),
                    'pointsTo' => $value,
                    'priority' => (string)(int)$opt,
                ];
            case 'TXT':
                return [
                    'host' => $this->fullHost($domain, $host),
                    'data' => $value,
                ];
            case 'SRV':
                $hostData = explode('.', $host);
                $optData = explode(' ', $opt);
                $serviceHost = implode('.', array_slice($hostData, 2));
                return [
                    'service' => isset($hostData[0]) ? $hostData[0] : '',
                    'protocol' => isset($hostData[1]) ? $hostData[1] : '',
                    'name' => $this->fullHost($domain, $serviceHost),
                    'priority' => isset($optData[0]) ? $optData[0] : '',
                    'weight' => isset($optData[1]) ? $optData[1] : '',
                    'port' => isset($optData[2]) ? $optData[2] : '',
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

    public function fullHost(\pm_Domain $domain, $host)
    {
        $host = rtrim($host, '.');
        $domainName = $domain->getName();
        if (empty($host)) {
            return "{$domainName}.";
        }
        if (strrpos($host, $domainName) === strlen($host) - strlen($domainName)) {
            return "{$host}.";
        }
        $host = "{$host}.{$domainName}.";

        return $host;
    }

    public function relativeHost(\pm_Domain $domain, $host)
    {
        $host = rtrim($host, '.');
        $domainName = $domain->getName();
        if (strrpos($host, $domainName) === strlen($host) - strlen($domainName)) {
            $host = substr($host, 0,  -strlen($domainName));
            $host = rtrim($host, '.');
        }

        return $host;
    }
}
