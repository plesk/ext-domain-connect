<?php
// Copyright 1999-2018. Plesk International GmbH.

use PleskExt\DomainConnect\DomainDns;

class DomainDnsTest extends PHPUnit\Framework\TestCase
{
    /** @var pm_Domain */
    private $domain;

    /** @var DomainDns */
    private $domainDns;

    public function setUp()
    {
        parent::setUp();
        $this->domain = $this->createMock(pm_Domain::class);
        $this->domain->method('getName')->willReturn('example.com');
        $this->domainDns = new DomainDns($this->domain);
    }

    public function testRelativeHost()
    {
        $this->assertEquals('test', $this->domainDns->relativeHost($this->domain, 'test.example.com.'));
        $this->assertEquals('test', $this->domainDns->relativeHost($this->domain, 'test.example.com'));
        $this->assertEquals('test', $this->domainDns->relativeHost($this->domain, 'test'));
    }

    public function testFullHost()
    {
        $this->assertEquals('test.example.com.', $this->domainDns->fullHost($this->domain, 'test'));
        $this->assertEquals('test.example.com.', $this->domainDns->fullHost($this->domain, 'test.example.com'));
        $this->assertEquals('example.com.', $this->domainDns->fullHost($this->domain, ''));
    }
}
