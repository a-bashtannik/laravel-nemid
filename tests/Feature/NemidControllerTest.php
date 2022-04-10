<?php

declare(strict_types=1);

namespace Feature;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Providers\NemidServiceProvider;

class NemidControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NemidServiceProvider::class,
        ];
    }

    public function testGetJavascript()
    {
        // act

        $response = $this->get('nemid/javascript');

        // assert

        $response->assertSuccessful();
        $response->assertJsonStructure(
            [
                'CLIENTFLOW',
                'DO_NOT_SHOW_CANCEL',
                'LANGUAGE',
                'SP_CERT',
                'TIMESTAMP',
                'PARAMS_DIGEST',
                'DIGEST_SIGNATURE'
            ]
        );
    }

    public function testGetCodefile()
    {
        // act

        $response = $this->get('nemid/codefile');

        // assert

        $response->assertSuccessful();
        $response->assertJsonStructure(
            [
                'CLIENTFLOW',
                'ISSUERDNFILTER',
                'LANGUAGE',
                'REQUESTISSUER',
                'SP_CERT',
                'TIMESTAMP',
                'PARAMS_DIGEST',
                'DIGEST_SIGNATURE'
            ]
        );
    }
}
