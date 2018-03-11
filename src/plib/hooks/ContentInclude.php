<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\Dns;
use PleskExt\DomainConnect\DomainConnect;

class Modules_DomainConnect_ContentInclude extends pm_Hook_ContentInclude
{
    private $warnings = [];

    public function init()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if (is_null($request)) {
            return;
        }
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        switch ("{$module}/{$controller}/${action}") {
            case 'smb//' :
            case 'smb/web/view' :
            case 'smb/web/overview' :
                // "Websites & Domains" page
                $this->getWebsitesAndDomainsMessages();
                break;
            case 'admin/domain/list' :
            case 'admin/subscription/list' :
                // Admin domains/subscriptions list
                $this->getDomainsListMessages();
                break;
            default:
                return;
        }
    }

    public function getWebsitesAndDomainsMessages()
    {
        foreach (\pm_Session::getCurrentDomains() as $domain) {
            $this->handleDomain($domain);
        }
    }

    public function getDomainsListMessages()
    {
        $client = \pm_Session::getClient();
        foreach (\pm_Domain::getAllDomains() as $domain) {
            if (!$client->isAdmin() && !$client->hasAccessToDomain($domain->getId())) {
                continue;
            }
            $this->handleDomain($domain);
        }
    }

    private function handleDomain(\pm_Domain $domain)
    {
        $domainConnect = new DomainConnect($domain);

        if (!$domainConnect->isEnabled()) {
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
            $url = $domainConnect->getApplyTemplateUrl(\pm_Config::get('webServiceId'), [
                'IP' => reset($hostingIps),
                'RANDOMTEXT' => 'test', // TODO: remove when "plesk" template will be ready
            ]);
        } catch (\pm_Exception $e) {
            \pm_Log::info($e->getMessage());
            return;
        }

        $message = \pm_Locale::lmsg('message.connect', [
            'domain' => $domain->getDisplayName(),
            'url' => "javascript:window.open('{$url}', '', 'width=750,height=750');",
        ]);
        $this->warnings[] = [$domain->getId(), $message];
    }

    public function getHeadContent()
    {
        if (empty($this->warnings)) {
            return '';
        }

        return '<script src="' . pm_Context::getBaseUrl() . 'domain-connect.js"></script>';
    }

    public function getJsOnReadyContent()
    {
        return implode("\n", array_map(function($warning) {
            list($domainId, $warningMsg) = $warning;
            $message = \Plesk_Base_Utils_String::safeForJs($warningMsg);
            return "PleskExt.DomainConnect.warnAboutDomainResolvingIssue($domainId, '$message');";
        }, $this->warnings));
    }
}
