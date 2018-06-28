<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

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
        throw new Exception\TemplateNotFound("Could not find template with providerId = '{$provider}' and serviceId = '{$service}'");
    }

    public function testRecords(\pm_Domain $domain, array $groups = [], array $parameters = [])
    {
        $domainRecords = $this->getDomainRecords($domain);
        $changes = [
            'toAdd' => [],
            'toRemove' => [],
        ];
        foreach ($this->getTemplateRecords($groups, $parameters) as $record) {
            if ($this->isExist($domainRecords, $record)) {
                continue;
            }
            $changes['toAdd'][] = $record;
            foreach ($this->getConflicts($domainRecords, $record) as $conflictRecord) {
                if ($this->isExist($changes['toRemove'], $conflictRecord)) {
                    continue;
                }
                $changes['toRemove'][] = $conflictRecord;
            }
        }
        return $changes;
    }

    public function getTemplateRecords(array $groups = [], array $parameters = [])
    {
        $records = [];
        foreach ((array)$this->data->records as $record) {
            if (!empty($groups) && (!isset($record->groupId) || !in_array($record->groupId, $groups, true))) {
                continue;
            }
            $this->validateRecord($record);
            $records[] = $this->prepareRecord($record, $parameters);
        }
        return $records;
    }

    private function getDomainRecords(\pm_Domain $domain)
    {
        $records = (new DomainDns($domain))->getRecords();
        foreach ($records as $record) {
            switch ($record->type) {
                case 'CNAME':
                case 'NS':
                case 'MX':
                    if (isset($record->pointsTo)) {
                        $record->pointsTo = rtrim($record->pointsTo, '.');
                    }
                    break;
                case 'SRV':
                    if (isset($record->target)) {
                        $record->target = rtrim($record->target, '.');
                    }
                    break;
            }
        }
        return $records;
    }

    private function validateRecord($record)
    {
        switch ($record->type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
            case 'NS':
                $requiredKeys = ['host', 'pointsTo', 'ttl'];
                break;
            case 'MX':
                $requiredKeys = ['host', 'pointsTo', 'priority', 'ttl'];
                break;
            case 'TXT':
                $requiredKeys = ['host', 'data', 'ttl'];
                break;
            case 'SRV':
                $requiredKeys = ['name', 'target', 'protocol', 'service', 'priority', 'weight', 'port', 'ttl'];
                break;
            default:
                throw new Exception\RecordNotSupported("Record type '{$record->type}' is not supported");
        }

        foreach ($requiredKeys as $key) {
            if (!isset($record->{$key})) {
                throw new \pm_Exception("Required field '{$key}' is missing in {$record->type} record");
            }
        }
    }

    private function prepareRecord(\stdClass $record, array $parameters)
    {
        if (empty($parameters['fqdn']) || '.' === $parameters['fqdn']) {
            throw new \pm_Exception("Required 'fqdn' parameter is missing");
        }
        $fqdn = $parameters['fqdn'];

        foreach (get_object_vars($record) as $key => $recordValue) {
            $recordValue = (string)$recordValue;
            foreach ($parameters as $variable => $value) {
                $recordValue = str_ireplace("%{$variable}%", $value, $recordValue);
            }

            $recordValue = str_replace('@', $fqdn, $recordValue);

            $record->{$key} = $recordValue;
        }

        switch ($record->type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
            case 'NS':
            case 'MX':
            case 'TXT':
                $record->host = DomainDns::fullHost($fqdn, $record->host);
                break;
            case 'SRV':
                $record->name = DomainDns::fullHost($fqdn, $record->name);
                break;
        }

        return $record;
    }

    private function isExist(array $domainRecords, \stdClass $record)
    {
        switch ($record->type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
            case 'NS':
                $keys = ['host', 'pointsTo'];
                break;
            case 'MX':
                $keys = ['host', 'pointsTo', 'priority'];
                break;
            case 'TXT':
                $keys = ['host', 'data'];
                break;
            case 'SRV':
                $keys = ['name', 'target', 'protocol', 'service', 'priority', 'weight', 'port'];
                break;
            default:
                return false;
        }

        foreach ($domainRecords as $domainRecord) {
            $matches = $domainRecord->type === $record->type;
            if (!$matches) {
                continue;
            }

            foreach ($keys as $key) {
                $domainValue = isset($domainRecord->{$key}) ? $domainRecord->{$key} : null;
                $recordValue = isset($record->{$key}) ? $record->{$key} : null;
                $matches = $matches && $domainValue === $recordValue;
            }
            if ($matches) {
                return true;
            }
        }
        return false;
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
                    if ($domainRecord->type === $record->type
                        && $domainRecord->host === $record->host) {
                        $conflicts[] = $domainRecord;
                    }
                    break;
                case 'TXT':
                    if ($domainRecord->type === $record->type
                        && $domainRecord->host === $record->host
                        && preg_match('/^v=(?<protocol>[^;\s])/', $record->data, $matches)
                        && 0 === strpos($domainRecord->data, "v={$matches['protocol']}")) {
                        $conflicts[] = $domainRecord;
                    }
                    break;
                case 'SRV':
                    if ($domainRecord->type === $record->type
                        && $domainRecord->name === $record->name
                        && $domainRecord->protocol === $record->protocol
                        && $domainRecord->service === $record->service) {
                        $conflicts[] = $domainRecord;
                    }
            }
        }
        return $conflicts;
    }

    public function applyChanges(\pm_Domain $domain, array $changes)
    {
        $domainDns = new DomainDns($domain);
        foreach ($changes['toRemove'] as $record) {
            $domainDns->removeRecord($record);
        }
        foreach ($changes['toAdd'] as $record) {
            $domainDns->addRecord($record);
        }
    }

    public function getProviderName()
    {
        return isset($this->data->providerName) ? $this->data->providerName : 'unknown';
    }

    public function getLogoUrl()
    {
        return isset($this->data->logoUrl) ? $this->data->logoUrl : '';
    }

    public function getRedirectDomain()
    {
        return isset($this->data->syncRedirectDomain) ? $this->data->syncRedirectDomain : '';
    }

    public function isSignatureRequired()
    {
        return !empty($this->data->syncPubKeyDomain);
    }

    public function verifySignature($query, $key, $signature)
    {
        if (empty($key) || empty($signature)) {
            throw new \pm_Exception('Request signature is required by the template');
        }

        if (false !== ($pos = strpos($query, '?'))) {
            $query = substr($query, $pos + 1);
        }
        $query = preg_replace('/&key=([^&]*|$)/', '', $query);
        $query = preg_replace('/&sig=([^&]*|$)/', '', $query);

        $pubKeyDomain = "{$key}.{$this->data->syncPubKeyDomain}";
        $pubKeyRecords = Dns::txtRecords($pubKeyDomain);
        $publicKey = $this->getPublicKey($pubKeyRecords);
        $result = openssl_verify($query, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);
        if (1 !== $result) {
            throw new \pm_Exception('Signature verification failed');
        }
    }

    private function getPublicKey(array $records)
    {
        $parts = array_map(function ($record) {
            $part = [
                'p' => 0,
                'a' => 'RS256',
                't' => 'x509',
                'd' => '',
            ];
            foreach (explode(',', $record) as $option) {
                list($key, $value) = explode('=', trim($option), 2);
                $part[$key] = $value;
            }
            return $part;
        }, $records);

        usort($parts, function ($part1, $part2) {
            return (int)$part1['p'] > (int)$part2['p'] ? 1 : -1;
        });

        $publicKeyData = join('', array_map(function ($part) {
            return $part['d'];
        }, $parts));

        if (empty($publicKeyData)) {
            throw new \pm_Exception('Could not find public key');
        }

        $publicKeyData = "-----BEGIN PUBLIC KEY-----\n{$publicKeyData}\n-----END PUBLIC KEY-----";
        $publicKey = openssl_get_publickey($publicKeyData);
        if (false === $publicKey) {
            throw new \pm_Exception('Invalid public key: ' . openssl_error_string());
        }
        return $publicKey;
    }
}
