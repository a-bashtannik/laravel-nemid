<?php

namespace Rentdesk\Nemid\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Services\CodeFileClient;

class CodeFileClientTest extends TestCase
{

    public function testGetLoginConfig()
    {
        // arrange

        $spcert = file_get_contents('tests/fixtures/sp-test.cert.pem');

        $jsClient = new CodeFileClient(
            file_get_contents('tests/fixtures/sp-test.key.pem'),
            $spcert,
            'TEST',
            'en'
        );

        // act

        $value = $jsClient->getLoginConfig();

        // assert

        $this->assertArrayHasKey('CLIENTFLOW', $value);
        $this->assertEquals('login', $value['CLIENTFLOW']);

        $this->assertArrayHasKey('ISSUERDNFILTER', $value);
        $this->assertEquals('VFJVU1QyNDA4', $value['ISSUERDNFILTER']);

        $this->assertArrayHasKey('LANGUAGE', $value);
        $this->assertEquals('en', $value['LANGUAGE']);

        $this->assertArrayHasKey('REQUESTISSUER', $value);
        $this->assertEquals(base64_encode('TEST'), $value['REQUESTISSUER']);

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
