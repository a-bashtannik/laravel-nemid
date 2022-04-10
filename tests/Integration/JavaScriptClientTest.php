<?php

namespace Rentdesk\Nemid\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Services\JavaScriptClient;

class JavaScriptClientTest extends TestCase
{

    public function testGetLoginConfig()
    {
        // arrange

        $spcert = file_get_contents('tests/fixtures/sp-test.cert.pem');

        $jsClient = new JavaScriptClient(
            file_get_contents('tests/fixtures/sp-test.key.pem'),
            $spcert,
            'TEST',
            'en'
        );

        // act

        $value = $jsClient->getLoginConfig();

        // assert

        $this->assertArrayHasKey('CLIENTFLOW', $value);
        $this->assertEquals('OCESLOGIN2', $value['CLIENTFLOW']);

        $this->assertArrayHasKey('DO_NOT_SHOW_CANCEL', $value);
        $this->assertEquals('TRUE', $value['DO_NOT_SHOW_CANCEL']);

        $this->assertArrayHasKey('LANGUAGE', $value);
        $this->assertEquals('en', $value['LANGUAGE']);

        $this->assertArrayHasKey('SP_CERT', $value);
        $this->assertEquals(
            preg_replace('/(-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s)/', '', $spcert),
            $value['SP_CERT']
        );

        $this->assertArrayHasKey('TIMESTAMP', $value);

        $this->assertArrayHasKey('PARAMS_DIGEST', $value);
        $this->assertArrayHasKey('DIGEST_SIGNATURE', $value);
    }
}
