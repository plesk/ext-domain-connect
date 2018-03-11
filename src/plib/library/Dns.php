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

    public static function txtRecord($domainName)
    {
        $dnsRecords = @dns_get_record($domainName, DNS_TXT);
        if (false === $dnsRecords) {
            $error = error_get_last()['message'];
            throw new \pm_Exception("Failed to resolve {$domainName}: {$error}");
        }
        foreach ($dnsRecords as $record) {
            if (empty($record['txt'])) {
                continue;
            }
            try {
                $url = \Zend_Uri_Http::fromString("https://{$record['txt']}");
            } catch(\Zend_Uri_Exception $e) {
                // not valid URL
                continue;
            }
            return $record['txt'];
        }
        throw new \pm_Exception("Could not find TXT DNS record for {$domainName}.");
    }
}
