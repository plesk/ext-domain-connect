<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\Dns;
use PleskExt\DomainConnect\DomainConnect;

class Modules_DomainConnect_ContentInclude extends pm_Hook_ContentInclude
{
    private $warnings = [];

    public function init()
    {
        if (!\pm_Config::get('serviceProvider')) {
            return;
        }

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

        $serviceId = \pm_Config::get('webServiceId');
        try {
            $url = $domainConnect->getApplyTemplateUrl($serviceId, [
                'ip' => reset($hostingIps),
            ]);
            $options = $domainConnect->getWindowOptions();
        } catch (\pm_Exception $e) {
            \pm_Log::info($e->getMessage());
            return;
        }

        $specs = implode(',', [
            "width={$options['width']}",
            "height={$options['height']}",
        ]);
        $message = \pm_Locale::lmsg('message.connect', [
            'domain' => $domain->getDisplayName(),
            'url' => $this->escapeHTML("javascript:window.open({$this->jsEscape($url)}, '', {$this->jsEscape($specs)});"),
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
            list($domainId, $message) = $warning;
            return "PleskExt.DomainConnect.warnAboutDomainResolvingIssue({$this->jsEscape($domainId)}, {$this->jsEscape($message)});";
        }, $this->warnings));
    }

    private function escapeHTML($data)
    {
        return htmlspecialchars($data);
    }

    private function jsEscape($data)
    {
        return json_encode($data);
    }
}
