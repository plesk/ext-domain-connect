<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.
namespace PleskExt\DomainConnect;

class DomainConnect
{
    private $providerId = 'exampleservice.domainconnect.org';
    private $domain;
    private $urlPrefix;
    private $data;

    public function __construct(\pm_Domain $domain)
    {
        $this->domain = $domain;
        $this->urlPrefix = Dns::txtRecord("_domainconnect.{$this->domain->getName()}");
    }

    private function getData()
    {
        if (null === $this->data) {
            $client = new \Zend_Http_Client("https://{$this->urlPrefix}/v2/{$this->domain->getName()}/settings");
            $response = $client->request(\Zend_Http_Client::GET);
            $this->data = \json_decode($response->getBody());
        }
        return $this->data;
    }

    public function getSyncUx()
    {
        return $this->getData()->urlSyncUX;
    }

    public function getApplyTemplateUrl($serviceId, array $properties)
    {
        $properties = array_merge([
            'domain' => $this->domain->getName(),
            'providerName' => "Plesk",
        ], $properties);
        $properties = \http_build_query($properties);
        return "{$this->getSyncUx()}/v2/domainTemplates/providers/{$this->providerId}/services/{$serviceId}/apply?{$properties}";
    }
}
