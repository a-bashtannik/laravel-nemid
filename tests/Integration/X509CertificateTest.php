<?php

namespace Rentdesk\Nemid\Tests\Integration;

use Carbon\Carbon;
use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Models\X509Certificate;

class X509CertificateTest extends TestCase
{
    public const POCES_CERTIFICATE_PATH = 'tests/fixtures/nemid-POCES-personal-valid.cert.pem';

    public function testGetSubject()
    {
        // arrange

        $x509 = X509Certificate::fromString(file_get_contents(self::POCES_CERTIFICATE_PATH));

        // act

        $value = $x509->getSubject('o');

        // assert

        $this->assertEquals('Ingen organisatorisk tilknytning', $value);
    }

    public function testHasSubject()
    {
        // arrange

        $x509 = X509Certificate::fromString(file_get_contents(self::POCES_CERTIFICATE_PATH));

        // act

        $valueTrue = $x509->hasSubject('o');
        $valueFalse = $x509->hasSubject('ou');

        // assert

        $this->assertTrue($valueTrue);
        $this->assertFalse($valueFalse);
    }

    public function testGetCommonName()
    {
        // arrange

        $x509 = X509Certificate::fromString(file_get_contents(self::POCES_CERTIFICATE_PATH));

        // act

        $value = $x509->getCommonName();

        // assert

        $this->assertEquals('Taina Karlsen', $value);
    }

    public function testGetValidNotBefore()
    {
        // arrange

        $x509 = X509Certificate::fromString(file_get_contents(self::POCES_CERTIFICATE_PATH));

        // act

        $value = $x509->getValidNotBefore();

        // assert

        $this->assertEquals(Carbon::parse('2021-04-30 08:05:19'), $value);
    }

    public function testGetValidNotAfter()
    {
        // arrange

        $x509 = X509Certificate::fromString(file_get_contents(self::POCES_CERTIFICATE_PATH));

        // act

        $value = $x509->getValidNotAfter();

        // assert

        $this->assertEquals(Carbon::parse('2024-04-30 08:35:19'), $value);
    }

    public function testGetCRLDistributionPoints()
    {
        // arrange

        $x509 = X509Certificate::fromString(file_get_contents(self::POCES_CERTIFICATE_PATH));

        // act

        $value = $x509->getCRLDistributionPoints();

        // assert

        $this->assertEquals(['http://crl.systemtest34.trust2408.com/systemtest34.crl'], $value);
    }

    public function testGetSerialNumber()
    {
        // arrange

        $x509 = X509Certificate::fromString(file_get_contents(self::POCES_CERTIFICATE_PATH));

        // act

        $value = $x509->getSerialNumber();

        // assert

        $this->assertEquals('1604078845', $value);
    }
}
