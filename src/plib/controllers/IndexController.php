<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\DomainConnect;

class IndexController extends pm_Controller_Action
{
    public function indexAction()
    {
        $this->view->pageTitle = $this->lmsg('title');
    }

    public function hideWarningAction()
    {
        if (!$this->_request->isPost()) {
            throw new pm_Exception('POST request is required.');
        }

        $domainId = $this->_request->getParam('domainId');

        if (!pm_Session::getClient()->hasAccessToDomain($domainId)) {
            throw new pm_Exception($this->lmsg('exceptions.clientHasNotAccessToDomain'));
        }

        $domain = new pm_Domain($domainId);
        $domainConnect = new DomainConnect($domain);
        $domainConnect->disable();
        $this->_helper->json([]);
    }

    public function configureLinkClickAction()
    {
        if (!$this->_request->isPost()) {
            throw new pm_Exception('POST request is required.');
        }

        $domainId = $this->_request->getParam('domainId');

        if (!pm_Session::getClient()->hasAccessToDomain($domainId)) {
            throw new pm_Exception($this->lmsg('exceptions.clientHasNotAccessToDomain'));
        }

        $domain = new pm_Domain($domainId);

        $domain->setSetting('configureLinkClicked', 1);

        $this->_helper->json([]);
    }
}
