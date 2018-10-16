<?php
// Copyright 1999-2018. Plesk International GmbH.

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
            case 'smb/web/view':
            case 'smb/web/overview':
                // "Websites & Domains" page
                $this->getWebsitesAndDomainsMessages();
                break;
            case 'admin/domain/list':
            case 'admin/subscription/list':
                // Admin domains/subscriptions list
                $this->getDomainsListMessages();
                break;
            case 'smb/dns-zone/records-list':
            case 'smb/dns-zone/who-is':
                $this->addDnsSettingsMessages($request->getParam('id'));
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

    public function addDnsSettingsMessages($idParam)
    {
        list($type, $domainId) = explode(':', $idParam, 2);
        if (in_array($type, ['d', 's']) && is_numeric($domainId)) {
            $domain = \pm_Domain::getByDomainId($domainId);
            $this->handleDomain($domain, true);
        }
    }

    private function handleDomain(\pm_Domain $domain, $permanentMessage = false)
    {
        $domainConnect = new DomainConnect($domain);

        if ($permanentMessage) {
            $domainConnect->init();
        }

        if (!$domainConnect->isEnabled()) {
            return;
        }

        if ($domainConnect->isConnected()) {
            return;
        }

        if (!$domainConnect->isConnectable()) {
            return;
        }

        $url = $domainConnect->getConfigureUrl();

        $message = \pm_Locale::lmsg('message.connect', [
            'domain' => $domain->getDisplayName(),
            'link' => '<a href="' . $this->escapeHTML($url) . '">' . \pm_Locale::lmsg('message.link') . '</a>',
        ]);

        $closable = !$permanentMessage;

        $this->warnings[] = [
            $domain->getId(),
            $message,
            $closable,
            $domainConnect->getWindowOptions(),
        ];
    }

    public function getHeadContent()
    {
        if (empty($this->warnings)) {
            return '';
        }

        return '<script src="' . pm_Context::getBaseUrl() . 'domain-connect.js?v1.1.1"></script>';
    }

    public function getJsOnReadyContent()
    {
        return implode("\n", array_map(function($warning) {
            return "PleskExt.DomainConnect.addConnectionMessage.apply(null, {$this->jsEscape($warning)});";
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
