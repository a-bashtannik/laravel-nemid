<?php

declare(strict_types=1);

namespace Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Models\MOCESCertificate;
use Rentdesk\Nemid\Providers\NemidServiceProvider;
use Rentdesk\Nemid\Services\NemidRevocationChecker;

class NemidRevocationCheckerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NemidServiceProvider::class,
        ];
    }

    public function testCheckCRLValid(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(
            file_get_contents('tests/fixtures/nemid-MOCES-employee-valid.cert.pem')
        );

        // act

        $value = NemidRevocationChecker::isRevokedCRL($cert);

        // assert

        $this->assertFalse($value);
    }

    public function testCheckCRLBlocked(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(
            file_get_contents('tests/fixtures/nemid-MOCES-employee-blocked.cert.pem')
        );

        // act

        $value = NemidRevocationChecker::isRevokedCRL($cert);

        // assert

        $this->assertTrue($value);
    }

    public function testCheckOCSPValid(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(
            file_get_contents('tests/fixtures/nemid-MOCES-employee-valid.cert.pem')
        );

        // act

        $value = NemidRevocationChecker::isRevokedOCSP($cert);

        // assert

        $this->assertFalse($value);
    }

    public function testCheckOCSPBlocked(): void
    {
        // arrange

        $cert = MOCESCertificate::fromString(
            file_get_contents('tests/fixtures/nemid-MOCES-employee-blocked.cert.pem')
        );

        // act

        $value = NemidRevocationChecker::isRevokedOCSP($cert);

        // assert

        $this->assertTrue($value);
    }
}
