<?php

namespace Rentdesk\Nemid\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Models\VOCESCertificate;

class VOCESCertificateTest extends TestCase
{
    public function testGetCvr()
    {
        // arrange

        $cert = VOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-VOCES-business-valid.cert.pem'));

        // act

        $cvr = $cert->getCvr();

        // assert

        $this->assertInstanceOf(VOCESCertificate::class, $cert);
        $this->assertEquals('30808460', $cvr);
    }

    public function testGetUid()
    {
        // arrange

        $cert = VOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-VOCES-business-valid.cert.pem'));

        // act

        $uid = $cert->getUid();

        // assert

        $this->assertInstanceOf(VOCESCertificate::class, $cert);
        $this->assertEquals('25351738', $uid);
    }

    public function testGetIdentity()
    {
        // arrange

        $cert = VOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-VOCES-business-valid.cert.pem'));

        // act

        $value = $cert->getIdentity();

        // assert

        $this->assertInstanceOf(VOCESCertificate::class, $cert);
        $this->assertEquals('NETS DANID A/S - TU VOCES gyldig', $value);
    }

    public function testGetOrganization()
    {
        // arrange

        $cert = VOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-VOCES-business-valid.cert.pem'));

        // act

        $value = $cert->getOrganization();

        // assert

        $this->assertInstanceOf(VOCESCertificate::class, $cert);
        $this->assertEquals('NETS DANID A/S // CVR:30808460', $value);
    }
}
