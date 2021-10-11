<?php
// Copyright 1999-2021. Plesk International GmbH.

namespace PleskExt\DomainConnect;

use PleskExt\DomainConnect\Exception\TemplateNotFound;

class PublicRequestHandler
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $protocol;

    /**
     * @var string
     */
    private $requestUri;

    /**
     * @var array
     */
    private $getParams = [];

    /**
     * @param $host
     * @param $protocol
     * @param $requestUri
     */
    public function __construct($host, $protocol, $requestUri)
    {
        $this->host = $host;
        $this->protocol = $protocol;
        $this->requestUri = $requestUri;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setGetParams(array $params)
    {
        $this->getParams = $params;
        return $this;
    }

    public function handle()
    {
        if (!\pm_Config::get('dnsProvider')) {
            $this->setHeader("{$this->protocol} 403 The DNS provider is disabled by server configuration", 400);
            return;
        }

        if (preg_match('|/v2/(.*)/settings|', $this->requestUri, $matches)) {
            $this->handleSettingsUri($matches[1]);
        } elseif (preg_match('|/v2/domainTemplates/providers/(.+)/services/(.+)|', $this->requestUri, $matches)) {
            $this->handleQueryTemplateUri($matches[1], $matches[2]);
        } else {
            $this->setHeader("{$this->protocol} 400 Bad Request", 400);
        }
    }

    /**
     * @param string $provider
     * @param string $service
     */
    private function handleQueryTemplateUri($provider, $service)
    {
        try {
            new Template($provider, $service);
        } catch (TemplateNotFound $e) {
            $this->setHeader("{$this->protocol} 404 Not found", 404);
            echo $e->getMessage();
        } catch (\pm_Exception $e) {
            $this->setHeader("{$this->protocol} 500 Internal server error", 500);
            echo $e->getMessage();
        }
    }

    /**
     * @param string $domain
     */
    private function handleSettingsUri($domain)
    {
        try {
            $pmDomain = \pm_Domain::getByName($domain);
        } catch (\pm_Exception $e) {
            $this->setHeader("{$this->protocol} 404 The domain does not belong to the DNS provider", 404);
            return;
        }

        $domainDns = new DomainDns($pmDomain);
        $data = array_filter([
            'providerId' => \pm_Config::get('providerId'),
            'providerName' => \pm_Config::get('providerName'),
            'providerDisplayName' => \pm_Config::get('providerDisplayName', $this->getGetParam('providerDisplayName')),
            'urlSyncUX' => "https://{$this->host}/modules/domain-connect/index.php/",
            'urlAPI' => "https://{$this->host}/modules/domain-connect/public/index.php/",
            'width' => 750,
            'height' => 750,
            'nameServers' => $domainDns->getNameServers(),
        ]);
        $this->setHeader('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * @param string $header
     * @param int $code
     */
    private function setHeader($header, $code = 0)
    {
        header($header, true, $code);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    private function getGetParam($name, $default = null)
    {
        return isset($this->getParams[$name]) ? $this->getParams[$name] : $default;
    }
}
