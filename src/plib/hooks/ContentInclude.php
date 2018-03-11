<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.

use PleskExt\DomainConnect\Dns;
use PleskExt\DomainConnect\DomainConnect;

class Modules_DomainConnect_ContentInclude extends pm_Hook_ContentInclude
{
    public function init()
    {
        $requestUri = $_SERVER["REQUEST_URI"];
        $domainUris = [
            '/smb/web/view',
            '/smb/web/overview',
        ];
        foreach ($domainUris as $uri) {
            if (0 === strpos($requestUri, $uri)) {
                foreach (\pm_Session::getCurrentDomains() as $domain) {
                    $this->handleDomain($domain);
                }
            }
        }
    }

    private function handleDomain(\pm_Domain $domain)
    {
        if (!$domain->getSetting('newDomain', false) && \pm_Config::get('newDomainsOnly')) {
            return;
        }
        if (!$domain->hasHosting()) {
            return;
        }
        $hostingIps = $domain->getIpAddresses();
        try {
            $resolvedIp = Dns::aRecord($domain->getName());
        } catch (\pm_Exception $e) {
            \pm_Log::err($e);
            $resolvedIp = '';
        }

        if (in_array($resolvedIp, $hostingIps)) {
            \pm_Log::info("Domain {$domain->getDisplayName()} is already resolved to the current server.");
            return;
        }
        \pm_Log::debug("Domain {$domain->getDisplayName()} is resolved to {$resolvedIp}, but expected " . join(' or ', $hostingIps));

        try {
            $domainConnect = new DomainConnect($domain);
            $url = $domainConnect->getApplyTemplateUrl('template1', [
                'IP' => reset($hostingIps),
                'RANDOMTEXT' => 'test',
            ]);
        } catch (\pm_Exception $e) {
            \pm_Log::info($e->getMessage());
            return;
        }

        $message = \pm_Locale::lmsg('message.connect', [
            'domain' => $domain->getDisplayName(),
            'url' => "javascript:window.open('{$url}', '', 'width=750,height=750');",
        ]);
        \pm_View_Status::addWarning($message, true);
    }
}
