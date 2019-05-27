<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

class Dns
{
    public static function aRecord($domainName)
    {
        $dnsRecord = @dns_get_record($domainName, DNS_A | DNS_AAAA);
        if (false === $dnsRecord) {
            $error = error_get_last()['message'];
            throw new \pm_Exception("Failed to resolve {$domainName}: {$error}");
        }

        if (isset($dnsRecord[0]['ip'])) {
            return $dnsRecord[0]['ip'];
        }
        if (isset($dnsRecord[0]['ipv6'])) {
            return $dnsRecord[0]['ipv6'];
        }
        throw new \pm_Exception("Could not find A/AAAA DNS record for {$domainName}.");
    }

    public static function txtRecords($domainName)
    {
        $dnsRecords = @dns_get_record($domainName, DNS_TXT);
        if (false === $dnsRecords) {
            $error = error_get_last()['message'];
            throw new \pm_Exception("Failed to resolve {$domainName}: {$error}");
        }
        return array_filter(
            array_map(function ($record) {
                return $record['txt'];
            }, $dnsRecords)
        );
    }

    /**
     * @param $domainName
     * @return mixed
     * @throws \pm_Exception
     */
    public static function mxRecord($domainName)
    {
        $dnsRecord = @dns_get_record($domainName, DNS_MX);
        if (false === $dnsRecord) {
            $error = error_get_last()['message'];
            throw new \pm_Exception("Failed to resolve {$domainName}: {$error}");
        }

        if (isset($dnsRecord[0]['host'])) {
            return ['host' => $dnsRecord[0]['host'], 'ip' => self::aRecord($dnsRecord[0]['host']), 'priority' => $dnsRecord[0]['pri']];
        }
        throw new \pm_Exception("Could not find MX DNS record for {$domainName}.");
    }

}
