<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\Template;
use PleskExt\DomainConnect\Exception\TemplateNotFound;

class V2Controller extends pm_Controller_Action
{
    public function domaintemplatesAction()
    {
        if ($this->isAction('apply')) {
            $this->forward('apply');
        } else {
            $this->forward('query');
        }
    }

    private function isAction($action)
    {
        // Zend_Request_Http does not return hasParam if it has no pair in the path
        return (bool)preg_match("#/{$action}(/\?|\?|/$|$)#", $this->getRequest()->getRequestUri());
    }

    public function queryAction()
    {
        $provider = $this->getParam('providers');
        $service = $this->getParam('services');

        try {
            new Template($provider, $service);
        } catch (TemplateNotFound $e) {
            $this->getResponse()
                ->setBody($this->view->escape($e->getMessage()))
                ->setHttpResponseCode(404);
        } catch (\pm_Exception $e) {
            $this->getResponse()
                ->setBody($this->view->escape($e->getMessage()))
                ->setHttpResponseCode(500);
        }

        $this->getHelper('viewRenderer')->setNoRender();
        $this->getHelper('layout')->disableLayout();
    }

    public function applyAction()
    {
        $provider = $this->getParam('providers');
        $service = $this->getParam('services');
        $domainName = $this->getParam('domain');
        $providerName = $this->getParam('providerName');
        $groups = array_filter(explode(',', $this->getParam('groupId', '')));
        $parameters = array_filter($this->getAllParams(), function ($param) {
            return !in_array($param, ['module', 'controller', 'action', 'providers', 'services', 'apply', 'domain', 'providerName']);
        }, ARRAY_FILTER_USE_KEY);

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

        $changes = $template->testRecords($domain, $groups, $parameters);
        if ($this->getRequest()->isPost()) {
            $template->applyChanges($domain, $changes);
            $this->forward('success');
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
        if (!pm_Session::getClient()->hasAccessToDomain($domain)) {
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

    public function successAction()
    {}
}
