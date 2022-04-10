<?php

namespace Rentdesk\Nemid\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Models\POCESCertificate;
use Sop\CryptoEncoding\PEM;

class POCESCertificateTest extends TestCase
{
    public function testIsYoung()
    {
        // arrange

        $cert = POCESCertificate::fromString(
            file_get_contents('tests/fixtures/nemid-POCES-personal-young-person.cert.pem')
        );

        // act

        $isYoung = $cert->isYoung();

        // assert

        $this->assertInstanceOf(POCESCertificate::class, $cert);
        $this->assertTrue($isYoung);
    }

    public function testGetPid()
    {
        // arrange

        $cert = POCESCertificate::fromString(file_get_contents('tests/fixtures/nemid-POCES-personal-valid.cert.pem'));

        // act

        $pid = $cert->getPid();

        // assert

        $this->assertInstanceOf(POCESCertificate::class, $cert);
        $this->assertEquals('9208-2002-2-175183276275', $pid);
    }
}
