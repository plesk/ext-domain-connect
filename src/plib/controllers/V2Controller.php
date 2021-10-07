<?php
// Copyright 1999-2021. Plesk International GmbH.

use PleskExt\DomainConnect\Template;

class V2Controller extends pm_Controller_Action
{
    public function init()
    {
        parent::init();

        if (!\pm_Config::get('dnsProvider')) {
            throw new pm_Exception($this->lmsg('exceptions.dnsProviderDisabled'));
        }
    }

    /**
     * @throws pm_Exception
     */
    public function domaintemplatesAction()
    {
        if (!$this->isAction('apply')) {
            throw new \pm_Exception('Bad request', 400);
        }
        $this->forward('apply');
    }

    private function isAction($action)
    {
        // Zend_Request_Http does not return hasParam if it has no pair in the path
        return (bool)preg_match("#/{$action}(/\?|\?|/$|$)#", $this->getRequest()->getRequestUri());
    }

    public function applyAction()
    {
        $provider = $this->getParam('providers');
        $service = $this->getParam('services');
        $domainName = $this->getParam('domain');
        $providerName = $this->getParam('providerName');

        $domain = \pm_Domain::getByName($domainName);
        $this->checkDomainAccess($domain);

        $template = new Template($provider, $service);
        if ($template->isSignatureRequired()) {
            $template->verifySignature(
                $this->getRequest()->getRequestUri(),
                $this->getParam('key'),
                $this->getParam('sig')
            );
        }

        $changes = $template->testRecords($domain, $this->getGroupsFilter(), $this->getSubstParams($domain));
        if ($this->getRequest()->isPost()) {
            \pm_Log::debug("Apply changes to DNS zone for domain {$domain->getName()}");

            $domain->setSetting('dns-apply-try-count', (int) $domain->getSetting('dns-apply-try-count',0)+1);
            $template->applyChanges($domain, $changes);
            $domain->setSetting('dns-for-provider-name', $provider);
            $domain->setSetting('dns-for-provider-template', $service);
            $domain->setSetting('dns-apply-success-count', (int) $domain->getSetting('dns-apply-success-count',0)+1);

            $this->forward('success');
            // render view of successAction using view's variables below
        }

        $this->view->domainName = $domainName;
        $this->view->providerName = $providerName ?: $template->getProviderName();
        $this->view->logoUrl = $template->getLogoUrl();
        $this->view->changes = $changes;
        $this->view->locale = \pm_Locale::getSection('apply');
        $this->view->redirectUri = $this->getRedirectUri($template);
    }

    private function checkDomainAccess(\pm_Domain $domain)
    {
        if (!pm_Session::getClient()->hasAccessToDomain($domain->getId())) {
            throw new \pm_Exception("Permission denied");
        }
    }

    private function getRedirectUri(Template $template)
    {
        if (!$this->hasParam('redirect_uri')) {
            return null;
        }
        $redirectUri = $this->getParam('redirect_uri');

        if ($template->isSignatureRequired()) {
            return $redirectUri;
        }

        if ($allowedDomain = $template->getRedirectDomain()) {
            $host = parse_url($redirectUri, PHP_URL_HOST);
            if ($host === $allowedDomain) {
                return $redirectUri;
            }
        }
        return null;
    }

    private function getGroupsFilter()
    {
        return array_filter(
            explode(',', $this->getParam('groupId', ''))
        );
    }

    private function getSubstParams(\pm_Domain $domain)
    {
        $parameters = $this->getAllParams();
        $parameters = array_filter($parameters, function ($param) {
            return !in_array($param, ['module', 'controller', 'action', 'providers', 'services', 'apply', 'domain', 'providerName']);
        }, ARRAY_FILTER_USE_KEY);

        $parameters['host'] = $this->getParam('host', null);
        $parameters['domain'] = $this->getParam('domain');
        $parameters['fqdn'] = "{$domain->getName()}.";
        if (!empty($parameters['host'])) {
            $parameters['fqdn'] = "{$parameters['host']}.{$parameters['fqdn']}";
        }

        return $parameters;
    }

    public function successAction()
    {}
}
