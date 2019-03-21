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
            'dns-provider' => []
        ];

        foreach (pm_Domain::getAllDomains() as $domain) {
            $connected = ((int) $domain->getSetting('connected', 0) > 0) ? true : false;
            $configureLinkClicked = ((int) $domain->getSetting('configureLinkClicked', 0)) ? true : false;
            $connectable = ((int) $domain->getSetting('connectable', 0)) ? true : false;

            if ($connected && $configureLinkClicked) {
                $stats['connectedDomains']++;
            }

            if ($connectable && !$connected) {
                $stats['potentialConnectableDomains']++;
            }

            $stats['dns-apply-try-count'] += (int) $domain->getSetting('dns-apply-try-count', 0);
            $stats['dns-apply-success-count'] += (int) $domain->getSetting('dns-apply-success-count', 0);
            $stats['dns-provider'][$domain->getSetting('dns-for-provider-name', "")][$domain->getSetting('dns-for-provider-template', "")]++;
        }
        unset($stats['dns-provider'][""][""]);

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
