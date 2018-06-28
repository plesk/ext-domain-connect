<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\DomainDns;

class DomainDnsTest extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dataRelativeHost
     * @param string $expected
     * @param string $fqdn
     * @param string $fullHost
     */
    public function testRelativeHost($expected, $fqdn, $fullHost)
    {
        $this->assertEquals($expected, DomainDns::relativeHost($fqdn, $fullHost));
    }

    public function dataRelativeHost()
    {
        return [
            ['test', 'example.com', 'test.example.com.'],
            ['test', 'example.com', 'test.example.com'],
            ['test', 'example.com', 'test'],
        ];
    }

    /**
     * @dataProvider dataFullHost
     * @param string $expected
     * @param string $fqdn
     * @param string $relativeHost
     */
    public function testFullHost($expected, $fqdn, $relativeHost)
    {
        $this->assertEquals($expected, DomainDns::fullHost($fqdn, $relativeHost));
    }

    public function dataFullHost()
    {
        return [
            ['test.example.com.', 'example.com', 'test'],
            ['test.example.com.', 'example.com.', 'test'],
            ['test.example.com.', 'example.com', 'test.example.com'],
            ['example.com.', 'example.com', ''],
        ];
    }
}
