<?php
// Copyright 1999-2018. Plesk International GmbH.
namespace PleskExt\DomainConnect;

class DomainConnect
{
    private $domain;
    private $urlPrefix;
    private $data;

    public function __construct(\pm_Domain $domain)
    {
        $this->domain = $domain;
    }

    private function getData()
    {
        if (null === $this->urlPrefix) {
            $this->urlPrefix = Dns::txtRecord("_domainconnect.{$this->domain->getName()}");
            \pm_Log::debug("TXT _domainconnect: {$this->urlPrefix}");
        }
        if (null === $this->data) {
            $url = "https://{$this->urlPrefix}/v2/{$this->domain->getName()}/settings";
            \pm_Log::debug("DomainConnect request: GET {$url}");
            try {
                $client = new \Zend_Http_Client($url);
                $response = $client->request(\Zend_Http_Client::GET);
            } catch (\Zend_Uri_Exception $e) {
                throw new \pm_Exception("Cannot fetch DomainConnect data: {$e->getMessage()}");
            }
            if ($response->getStatus() !== 200) {
                throw new \pm_Exception("Cannot fetch DomainConnect data {$response->getStatus()}: {$response->getMessage()}");
            }
            \pm_Log::debug("DomainConnect response {$response->getStatus()}: {$response->getBody()}");
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
        $providerId = \pm_Config::get('providerId');
        $properties = array_merge([
            'domain' => $this->domain->getName(),
            'providerName' => "Plesk",
        ], $properties);
        $properties = \http_build_query($properties);
        return "{$this->getSyncUx()}/v2/domainTemplates/providers/{$providerId}/services/{$serviceId}/apply?{$properties}";
    }

    public function enable()
    {
        $this->domain->setSetting('enabled', true);
    }

    public function disable()
    {
        $this->domain->setSetting('enabled', false);
    }

    public function isEnabled()
    {
        if (!\pm_Config::get('newDomainsOnly')) {
            return 'false' !== $this->domain->getSetting('enabled');
        }

        return 'true' === $this->domain->getSetting('enabled');
    }
}
