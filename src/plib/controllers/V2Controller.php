<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\Template;

class V2Controller extends pm_Controller_Action
{
    public function domaintemplatesAction()
    {
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
        $provider = $this->getParam('providers');
        $service = $this->getParam('services');
        $domainName = $this->getParam('domain');
        $providerName = $this->getParam('providerName');
        $groups = array_filter(explode(',', $this->getParam('groupId', '')));
        $parameters = array_filter($this->getAllParams(), function ($param) {
            return !in_array($param, ['module', 'controller', 'action', 'providers', 'services', 'apply', 'domain', 'providerName']);
        }, ARRAY_FILTER_USE_KEY);

        $domain = \pm_Domain::getByName($domainName);
        $template = new Template($provider, $service);
        $changes = $template->testRecords($domain, $groups, $parameters);
        if ($this->getRequest()->isPost()) {
            $template->applyChanges($domain, $changes);
            $this->_status->addInfo(\pm_Locale::lmsg('apply.success', ['domain' => $domainName]));
            $this->redirect('/', ['prependBase' => false]);
        }
        $this->view->form = $this->getConfirmationForm($domainName, $providerName, $changes);
    }

    private function getConfirmationForm($domainName, $providerName, array $changes)
    {
        $form = new pm_Form_Simple();
        $form->addElement('description', 'description', [
            'description' => \pm_Locale::lmsg('apply.description', [
                'domain' => $this->view->escape($domainName),
                'providerName' => $this->view->escape($providerName),
            ]),
            'escape' => false,
        ]);

        if (!empty($changes['toRemove'])) {
            $form->addElement('description', 'toRemove', [
                'description' => \pm_Locale::lmsg('apply.toRemove'),
            ]);
        }
        foreach ($changes['toRemove'] as $record) {
            $form->addElement('simpleText', "toRemove{$this->recordId($record)}", [
                'value' => $this->displayRecord($record),
            ]);
        }
        if (!empty($changes['toAdd'])) {
            $form->addElement('description', 'toAdd', [
                'description' => \pm_Locale::lmsg('apply.toAdd'),
            ]);
        }
        foreach ($changes['toAdd'] as $record) {
            $form->addElement('simpleText', "toAdd{$this->recordId($record)}", [
                'value' => $this->displayRecord($record),
            ]);
        }

        $form->addElement('description', 'action', [
            'description' => \pm_Locale::lmsg('apply.action', [
                'providerName' => $this->view->escape($providerName),
            ]),
            'escape' => false,
        ]);
        $form->addControlButtons([
            'sendTitle' => \pm_Locale::lmsg('apply.connectButton'),
            'hideLegend' => true,
        ]);
        return $form;
    }

    private function recordId(\stdClass $record)
    {
        return md5(json_encode($record));
    }

    private function displayRecord(\stdClass $record)
    {
        switch ($record->type) {
            case 'A':
            case 'AAAA':
            case 'CNAME':
                return "{$record->host} IN {$record->type} {$record->pointsTo}";
            case 'TXT':
                return "{$record->host} IN {$record->type} {$record->data}";
            default:
                return "{$record->host} IN {$record->type}";
        }
    }
}
