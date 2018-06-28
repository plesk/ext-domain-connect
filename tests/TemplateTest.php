<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\Template;

class TemplateTest extends PHPUnit\Framework\TestCase
{
    /**
     * @param string $provider
     * @param string $service
     * @dataProvider getResources
     */
    public function testGetTemplateRecords($provider, $service)
    {
        $template = new Template($provider, $service);
        $records = $template->getTemplateRecords([], ['fqdn' => 'example.com.']);
        $this->assertNotEmpty($records);
    }

    public function getResources()
    {
        $templatesDir = __DIR__ . '/../src/plib/resources/templates';
        return array_map(
            function ($file) {
                $rawData = \file_get_contents($file);
                $data = \json_decode($rawData);
                return [
                    $data->providerId,
                    $data->serviceId,
                ];
            },
            glob("{$templatesDir}/*.json")
        );
    }
}
