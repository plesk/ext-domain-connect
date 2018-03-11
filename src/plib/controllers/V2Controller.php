<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.

use PleskExt\DomainConnect\Template;

class V2Controller extends pm_Controller_Action
{
    private $provider;
    private $service;

    public function domaintemplatesAction()
    {
        $this->provider = $this->getParam('providers');
        $this->service = $this->getParam('services');
        if ($this->isAction('apply')) {
            $this->forward('apply');
        }
    }

    private function isAction($action)
    {
        // Zend_Request_Http does not return hasParam if it has no pair in the path
        return (bool)preg_match("#/{$action}(/\?|\?|/$|$)#", $this->getRequest()->getRequestUri());
    }

    public function applyAction()
    {
        $domain = $this->getParam('domain');
        $providerName = $this->getParam('providerName');
        $parameters = array_filter($this->getAllParams(), function ($param) {
            return !in_array($param, ['providers', 'services', 'apply', 'domain', 'providerName']);
        }, ARRAY_FILTER_USE_KEY);

        $template = new Template($this->provider, $this->service);

        $form = new pm_Form_Simple();
        $form->addElement('description', 'description', [
            'description' => \pm_Locale::lmsg('apply.description', [
                'domain' => $this->view->escape($domain),
                'providerName' => $this->view->escape($providerName),
            ]),
            'escape' => false,
        ]);
        // TODO show changes based on template
        $form->addElement('description', 'action', [
            'description' => \pm_Locale::lmsg('apply.action', [
                'providerName' => $this->view->escape($providerName),
            ]),
            'escape' => false,
        ]);
        $form->addControlButtons(['hideLegend' => true]);
        $this->view->form = $form;
    }
}
