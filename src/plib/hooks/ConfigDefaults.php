<?php
// Copyright 1999-2018. Plesk International GmbH.

class Modules_DomainConnect_ConfigDefaults extends pm_Hook_ConfigDefaults
{
    public function getDefaults()
    {
        return [
            'serviceProvider' => true,
            'dnsProvider' => true,
            'providerId' => 'plesk.com',
            'providerName' => 'Plesk',
            'webServiceId' => 'web',
            'mailServiceId' => 'mail',
            'webServiceGroupId' => 'WebService',
        ];
    }
}
