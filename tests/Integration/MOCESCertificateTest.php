<?php

namespace Rentdesk\Nemid\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Models\MOCESCertificate;

class MOCESCertificateTest extends TestCase
{
    public function testGetCvr(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-MOCES-employee-valid.cert.pem'));

        // act

        $value = $cert->getCvr();

        // assert

        $this->assertInstanceOf(MOCESCertificate::class, $cert);
        $this->assertEquals('30808460', $value);
    }

    public function testGetRid(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-MOCES-employee-valid.cert.pem'));

        // act

        $value = $cert->getRid();

        // assert

        $this->assertInstanceOf(MOCESCertificate::class, $cert);
        $this->assertEquals('45490598', $value);
    }

    public function testGetIdentity(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-MOCES-employee-valid.cert.pem'));

        // act

        $value = $cert->getIdentity();

        // assert

        $this->assertInstanceOf(MOCESCertificate::class, $cert);
        $this->assertEquals('TU GENEREL MOCES gyldig', $value);
    }

    public function testGetOrganization(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-MOCES-employee-valid.cert.pem'));

        // act

        $value = $cert->getOrganization();

        // assert

        $this->assertInstanceOf(MOCESCertificate::class, $cert);
        $this->assertEquals('NETS DANID A/S // CVR:30808460', $value);
    }
}
