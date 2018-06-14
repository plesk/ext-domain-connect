<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\Installer;

class InstallerTest extends PHPUnit\Framework\TestCase
{
    /**
     * @param string $hostname
     * @param string $placeholder
     * @dataProvider dataPleskHost
     */
    public function testGetPleskHost($hostname, $placeholder)
    {
        $apiClient = $this->getMockBuilder(\PleskX\Api\Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverOperator = $this->getMockBuilder(\PleskX\Api\Operator\Server::class)
            ->setConstructorArgs([$apiClient])
            ->getMock();
        $apiClient->method('server')
            ->willReturn($serverOperator);
        $generalInfo = $this->getMockBuilder(\PleskX\Api\Struct\Server\GeneralInfo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serverOperator->method('getGeneralInfo')
            ->willReturn($generalInfo);
        /** @var \PleskX\Api\Struct\Server\GeneralInfo $generalInfo */
        $generalInfo->serverName = $hostname;

        $installer = new Installer($apiClient);
        $this->assertEquals($placeholder, $installer->getPleskHost());
    }

    public function dataPleskHost()
    {
        return [
            ['google.com', '<hostname>'],
            ['ec2-18-205-105-166.compute-1.amazonaws.com', '<hostname>'],
            ['ip-AC1F55FB.local', '<ip>'],
            ['localhost', '<ip>'],
            ['a10-52-42-176', '<ip>'],
        ];
    }
}
