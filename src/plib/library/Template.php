<?php
// Copyright 1999-2018. Plesk International GmbH. All rights reserved.
namespace PleskExt\DomainConnect;

class Template
{
    private $data;

    public function __construct($provider, $service)
    {
        $this->data = $this->getData($provider, $service);
    }

    private function getData($provider, $service)
    {
        $templatesDir = __DIR__ . '/../resources/templates';
        foreach (glob("{$templatesDir}/*.json") as $file) {
            $rawData = file_get_contents($file);
            $data = json_decode($rawData);
            if ($data->providerId === $provider && $data->serviceId === $service) {
                return $data;
            }
        }
        throw new \pm_Exception("Could not find template with providerId = '{$provider}' and serviceId = '{$service}'");
    }
}
