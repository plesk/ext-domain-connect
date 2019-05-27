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
                $this->addDnsSettingsMessages($request->getParam('id'), $request->getParam('type'));
                break;
            case 'smb/mail-settings/edit':
            case 'smb/mail-settings/index':
            case 'smb/email-address/list':
                // Mail settings and emails list
                $this->handleService($request->getParam('domainId'), \pm_Config::get('mailServiceId'));
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

    public function addDnsSettingsMessages($idParam, $typeParam)
    {
        if (strpos($idParam, ':') !== false) {
            list($type, $domainId) = explode(':', $idParam, 2);

            if (in_array($type, ['d', 's']) && is_numeric($domainId)) {
                try {
                    $domain = \pm_Domain::getByDomainId($domainId);
                } catch (pm_Exception $e) {
                    \pm_Log::warn($e);
                    return;
                }
                $this->handleDomain($domain, true);
            }
        } elseif ($typeParam === 'domain') {
            try {
                $domain = \pm_Domain::getByDomainId($idParam);
            } catch (pm_Exception $e) {
                \pm_Log::warn($e);
                return;
            }
            $this->handleDomain($domain, true);
        }
    }

    public function handleService($domainId, $serviceId, $permanentMessage = false)
    {
        try {
            $domain = \pm_Domain::getByDomainId($domainId);
        } catch (\pm_Exception $e) {
            pm_Log::warn($e);
            return;
        }
        $domainConnect = new DomainConnect($domain, $serviceId);

        if ($permanentMessage) {
            $domainConnect->initService();
        }

        if (!$domainConnect->isEnabled($serviceId)) {
            return;
        }

        if ($domainConnect->isConnected($serviceId)) {
            return;
        }

        if (!$domainConnect->isConnectable($serviceId)) {
            return;
        }

        $url = $domainConnect->getConfigureUrl($serviceId);

        $message = \pm_Locale::lmsg(
            'message.' . $serviceId . 'connect',
            [
                'domain' => $domain->getDisplayName(),
                'link' => '<a href="' . $this->escapeHTML($url) . '">' . \pm_Locale::lmsg('message.link') . '</a>',
            ]
        );

        $closable = !$permanentMessage;

        $this->warnings[] = [
            $domain->getId(),
            $message,
            $closable,
            $domainConnect->getWindowOptions(),
        ];
    }

    private function handleDomain(\pm_Domain $domain, $permanentMessage = false)
    {
        $domainConnect = new DomainConnect($domain, \pm_Config::get('webServiceId'));

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

        $message = \pm_Locale::lmsg(
            'message.connect',
            [
            'domain' => $domain->getDisplayName(),
            'link' => '<a href="' . $this->escapeHTML($url) . '">' . \pm_Locale::lmsg('message.link') . '</a>',
            ]
        );

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
