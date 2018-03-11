<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

use PleskExt\DomainConnect\DomainDns;

class Template
{
    private $data;

    public function __construct($provider, $service)
    {
        $this->data = $this->getData($provider, $service);
    }

    private function getData($provider, $service)
    {
        $templatesDir = __DIR__ . '/../resources/templates';
        foreach (glob("{$templatesDir}/*.json") as $file) {
            $rawData = \file_get_contents($file);
            $data = \json_decode($rawData);
            if ($data->providerId === $provider && $data->serviceId === $service) {
                return $data;
            }
        }
        throw new \pm_Exception("Could not find template with providerId = '{$provider}' and serviceId = '{$service}'");
    }

    public function testRecords(\pm_Domain $domain, array $groups = [], array $parameters = [])
    {
        $domainRecords = $this->getDomainRecords($domain);
        $changes = [
            'toAdd' => [],
            'toRemove' => [],
        ];
        foreach ((array)$this->data->records as $record) {
            if (!empty($groups) && (!isset($record->groupId) || !in_array($record->groupId, $groups))) {
                continue;
            }
            $record = $this->prepareRecord($record, $parameters);

            $changes['toAdd'][] = $record;
            $changes['toRemove'] = array_merge(
                $changes['toRemove'],
                $this->getConflicts($domainRecords, $record)
            );
        }
        return $changes;
    }

    private function getDomainRecords(\pm_Domain $domain)
    {
        return (new DomainDns)->getRecords($domain);
    }

    private function prepareRecord(\stdClass $record, array $parameters)
    {
        $keys = ['host', 'pointsTo', 'data'];
        foreach ($parameters as $variable => $value) {
            foreach ($keys as $key) {
                if (isset($record->{$key})) {
                    $record->{$key} = str_ireplace("%{$variable}%", $value, $record->{$key});
                }
            }
        }
        return $record;
    }

    private function getConflicts(array $domainRecords, \stdClass $record)
    {
        $conflicts = [];
        foreach ($domainRecords as $domainRecord) {
            switch ($record->type) {
                case 'A':
                case 'AAAA':
                case 'CNAME':
                    if (in_array($domainRecord->type, ['A', 'AAAA', 'CNAME'])
                        && $domainRecord->host === $record->host) {
                        $conflicts[] = $domainRecord;
                    }
                    break;
                case 'MX':
                case 'SRV':
                    if ($domainRecord->type === $record->type
                        && $domainRecord->host === $record->host) {
                        $conflicts[] = $domainRecord;
                    }
                    break;
                case 'TXT':
                    break;
            }
        }
        return $conflicts;
    }
}
