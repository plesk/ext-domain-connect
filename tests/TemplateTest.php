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

    /**
     * @dataProvider dataGetConflicts
     * @param array $record
     * @param array $conflicts
     */
    public function testGetConflicts(array $record, array $conflicts)
    {
        $domainRecords = array_map(function ($record) {
            return (object)$record;
        }, [
            ['id' => 2225, 'type' => 'NS', 'host' => 'ekazakov.ru.', 'pointsTo' => 'ns1.ekazakov.ru'],
            ['id' => 2226, 'type' => 'A', 'host' => 'ns1.ekazakov.ru.', 'pointsTo' => '37.139.7.82'],
            ['id' => 2227, 'type' => 'NS', 'host' => 'ekazakov.ru.', 'pointsTo' => 'ns2.ekazakov.ru'],
            ['id' => 2228, 'type' => 'A', 'host' => 'ns2.ekazakov.ru.', 'pointsTo' => '37.139.7.82'],
            ['id' => 2229, 'type' => 'A', 'host' => 'ekazakov.ru.', 'pointsTo' => '37.139.7.82'],
            ['id' => 2230, 'type' => 'A', 'host' => 'webmail.ekazakov.ru.', 'pointsTo' => '37.139.7.82'],
            ['id' => 2231, 'type' => 'MX', 'host' => 'ekazakov.ru.', 'pointsTo' => 'mail.ekazakov.ru', 'priority' => '10'],
            ['id' => 2232, 'type' => 'A', 'host' => 'mail.ekazakov.ru.', 'pointsTo' => '37.139.7.82'],
            ['id' => 2233, 'type' => 'A', 'host' => 'ipv4.ekazakov.ru.', 'pointsTo' => '37.139.7.82'],
            ['id' => 2234, 'type' => 'CNAME', 'host' => 'www.ekazakov.ru.', 'pointsTo' => 'ekazakov.ru'],
            ['id' => 2235, 'type' => 'TXT', 'host' => 'ekazakov.ru.', 'data' => 'v=spf1 +a +mx +a:ekazakov-seo.plesk.space -all'],
            ['id' => 2236, 'type' => 'TXT', 'host' => '_dmarc.ekazakov.ru.', 'data' => 'v=DMARC1; p=none'],
            ['id' => 2237, 'type' => 'TXT', 'host' => '_domainconnect.ekazakov.ru.', 'data' => 'domainconnect.plesk.com/host/ekazakov-seo.plesk.space/port/8443'],
        ]);
        $expected = array_map(function ($record) {
            return (object)$record;
        }, $conflicts);

        $this->assertEquals($expected, Template::getConflicts($domainRecords, (object)$record));
    }

    public function dataGetConflicts()
    {
        return [
            [
                ['groupId' => 'Outlook', 'type' => 'TXT', 'host' => 'ekazakov.ru.', 'data' => 'v=spf1 include:spf.protection.outlook.com -all', 'ttl' => '3600'],
                [
                    ['id' => 2235, 'type' => 'TXT', 'host' => 'ekazakov.ru.', 'data' => 'v=spf1 +a +mx +a:ekazakov-seo.plesk.space -all'],
                ]
            ],
            [
                ['groupId' => 'Outlook', 'type' => 'MX', 'host' => 'ekazakov.ru.', 'pointsTo' => 'ekazakov-ru.mail.protection.outlook.com', 'priority' => '0', 'ttl' => '3600'],
                [
                    ['id' => 2231, 'type' => 'MX', 'host' => 'ekazakov.ru.', 'pointsTo' => 'mail.ekazakov.ru', 'priority' => '10'],
                ]
            ],
            [
                ['groupId' => 'Outlook', 'type' => 'MX', 'host' => 'www.ekazakov.ru.', 'pointsTo' => 'ekazakov-ru.mail.protection.outlook.com', 'priority' => '0', 'ttl' => '3600'],
                [
                    ['id' => 2234, 'type' => 'CNAME', 'host' => 'www.ekazakov.ru.', 'pointsTo' => 'ekazakov.ru'],
                ]
            ],
        ];
    }
}
