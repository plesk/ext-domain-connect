<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.

class Modules_DomainConnect_ConfigDefaults extends pm_Hook_ConfigDefaults
{
    public function getDefaults()
    {
        return [
            'newDomainsOnly' => true,
            'providerId' => 'exampleservice.domainconnect.org',  // TODO: replace w/ "plesk"
            'webServiceId' => 'template1', // TODO: replace w/ "web"
        ];
    }
}
