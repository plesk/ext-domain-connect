<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\Template;

class IndexController extends pm_Controller_Action
{
    public function indexAction()
    {
        // TODO: add main info screen
    }

    public function hideWarningAction()
    {
        $domainId = $this->_request->getParam('domainId');
        $domain = new pm_Domain($domainId);
        $domainConnect = new \PleskExt\DomainConnect\DomainConnect($domain);
        $domainConnect->disable();
        $this->_helper->json([]);
    }

}
