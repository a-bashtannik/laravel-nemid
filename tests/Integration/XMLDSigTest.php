<?php

namespace Rentdesk\Nemid\Tests\Integration;

use Carbon\Carbon;
use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Exceptions\InvalidCertificateException;
use Rentdesk\Nemid\Models\POCESCertificate;
use Rentdesk\Nemid\Models\XMLDSig;
use Rentdesk\Nemid\Providers\NemidServiceProvider;

class XMLDSigTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NemidServiceProvider::class,
        ];
    }

    public function testGetLeafCertificatePoces(): void
    {
        // arrange

        $nemIdDecodedResponse = file_get_contents('tests/fixtures/XMLDSigMessage-POCES.xml');

        // act

        $XMLDSigMessage = new XMLDSig($nemIdDecodedResponse);
        $leafCertificate = $XMLDSigMessage->getLeafCertificate();

        // assert

        $this->assertInstanceOf(POCESCertificate::class, $leafCertificate);
    }

    public function testIsValid(): void
    {
        // arrange

        $nemIdDecodedResponse = file_get_contents('tests/fixtures/XMLDSigMessage-POCES.xml');

        // act

        $XMLDSigMessage = new XMLDSig($nemIdDecodedResponse);
        $value = $XMLDSigMessage->validate();

        // assert

        $this->assertTrue($value);
    }

    public function testGetTimestamp(): void
    {
        // arrange

        $nemIdDecodedResponse = file_get_contents('tests/fixtures/XMLDSigMessage-POCES.xml');

        // act

        $XMLDSigMessage = new XMLDSig($nemIdDecodedResponse);
        $timestamp = $XMLDSigMessage->getTimestamp();

        // assert

        $this->assertEquals(Carbon::createFromFormat('Y-m-d H:i:sO', '2021-04-20 15:26:05+0200'), $timestamp);
    }

    public function testGetTimestampBase64(): void
    {
        // arrange

        $nemIdDecodedResponse = file_get_contents('tests/fixtures/XMLDSigMessage-POCES-encoding-base64.xml');

        // act

        $XMLDSigMessage = new XMLDSig($nemIdDecodedResponse);
        $timestamp = $XMLDSigMessage->getTimestamp();

        // assert

        $this->assertEquals(Carbon::createFromFormat('Y-m-d H:i:sO', '2021-05-11 20:10:37+0200'), $timestamp);
    }

    public function testGetTimestampMissing(): void
    {
        // arrange

        $nemIdDecodedResponse = file_get_contents('tests/fixtures/XMLDSigMessage-MOCES.xml');

        // act

        $XMLDSigMessage = new XMLDSig($nemIdDecodedResponse);
        $timestamp = $XMLDSigMessage->getTimestamp();

        // assert

        $this->assertNull($timestamp);
    }

    public function testExiredCertificateThrowsException(): void
    {
        // arrange

        $nemIdDecodedResponse = file_get_contents('tests/fixtures/XMLDSigMessage-MOCES-expired.xml');
        $this->expectException(InvalidCertificateException::class);
        $this->expectExceptionMessage('Certificate has expired');

        // act

        $XMLDSigMessage = new XMLDSig($nemIdDecodedResponse);
        $XMLDSigMessage->validate();
    }
}
