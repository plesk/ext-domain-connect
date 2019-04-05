<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.

class Modules_DomainConnect_CustomInfo implements pm_Hook_Interface
{
    /**
     * @return array
     */
    private function getStats()
    {
        $stats = [
            'connectedDomains' => 0,
            'potentialConnectableDomains' => 0,
            'dns-apply-try-count' => 0,
            'dns-apply-success-count' => 0,
            'dns-provider' => ["" => ["" => 0]]
        ];

        foreach (pm_Domain::getAllDomains() as $domain) {
            $connected = ((int) $domain->getSetting('connected', 0) > 0) ? true : false;
            $configureLinkClicked = ((int) $domain->getSetting('configureLinkClicked', 0)) ? true : false;
            $connectable = ((int) $domain->getSetting('connectable', 0)) ? true : false;
            $providerName = $domain->getSetting('dns-for-provider-name', "");
            $providerTemplate = $domain->getSetting('dns-for-provider-template', "");

            if ($connected && $configureLinkClicked) {
                $stats['connectedDomains']++;
            }

            if ($connectable && !$connected) {
                $stats['potentialConnectableDomains']++;
            }

            $stats['dns-apply-try-count'] += (int) $domain->getSetting('dns-apply-try-count', 0);
            $stats['dns-apply-success-count'] += (int) $domain->getSetting('dns-apply-success-count', 0);
            if (!isset($stats['dns-provider'][$providerName])) {
                $stats['dns-provider'][$providerName] = [$providerTemplate => 1];
            } elseif (!isset($stats['dns-provider'][$providerName][$providerTemplate])) {
                $stats['dns-provider'][$providerName][$providerTemplate] = 1;
            } else {
                $stats['dns-provider'][$providerName][$providerTemplate]++;
            }
        }
        unset($stats['dns-provider'][""]);

        return $stats;
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return json_encode($this->getStats());
    }
}
