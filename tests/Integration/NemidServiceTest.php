<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Tests\Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Facades\Nemid;
use Rentdesk\Nemid\Providers\NemidServiceProvider;
use Rentdesk\Nemid\Services\CodeFileClient;
use Rentdesk\Nemid\Services\JavaScriptClient;
use Rentdesk\Nemid\Services\NemidService;

class NemidServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NemidServiceProvider::class,
        ];
    }

    public function testNemidServiceLoaded()
    {
        // arrange

        $this->assertTrue($this->app->providerIsLoaded(NemidServiceProvider::class));

        // act

        /** @var NemidService $nemidService */
        $nemidService = app()->make('nemid');

        // assert

        $this->assertInstanceOf(NemidService::class, $nemidService);
        $this->assertInstanceOf(JavaScriptClient::class, $nemidService->javaScriptClient());
        $this->assertInstanceOf(CodeFileClient::class, $nemidService->codeFileClient());
    }

    public function testNemidFacade()
    {
        $this->assertInstanceOf(JavaScriptClient::class, Nemid::javaScriptClient());
        $this->assertInstanceOf(CodeFileClient::class, Nemid::codeFileClient());
    }
}
