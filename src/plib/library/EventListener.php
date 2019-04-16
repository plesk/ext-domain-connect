<?php
// Copyright 1999-2018. Plesk International GmbH.

class Modules_DomainConnect_EventListener implements EventListener
{
    public function filterActions()
    {
        return [
            'domain_create',
            'site_create',
            'subdomain_create',
        ];
    }

    public function handleEvent($objectType, $objectId, $action, $oldValues, $newValues)
    {
        if (in_array($action, $this->filterActions())) {
            $domain = \pm_Domain::getByDomainId($objectId);
            $domainConnect = new \PleskExt\DomainConnect\DomainConnect($domain);

            $domainConnect->init();
        }
    }
}

// Workaround for bug PPP-33009
pm_Context::init('domain-connect');

return new Modules_DomainConnect_EventListener();
