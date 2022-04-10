<?php

declare(strict_types=1);

namespace Integration;

use Orchestra\Testbench\TestCase;
use Rentdesk\Nemid\Providers\NemidServiceProvider;
use Rentdesk\Nemid\Services\NemidWebservice;

class NemidWebserviceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            NemidServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $ip = file_get_contents('http://ipecho.net/plain');

        if ($ip !== '165.227.167.75') {
            $this->markTestSkipped('NemID webserives are behind of the firewall, use whitelisted IP address');
        }
    }

    /**
     * 1. Create new user on https://appletk.danid.dk/developers/
     * 2. Wait for certificate generation (on the bottom of the page)
     * 3. Download and take PID value from the subject: serialNumber (Serial Number): PID:9208-2002-2-193823564960
     *
     * @throws \SoapFault
     */
    public function testCheckPid(): void
    {
        // arrange

        $webservice = new NemidWebservice(true);

        // act

        $value = $webservice->checkPid('2504050258', '9208-2002-2-193823564960');

        // assert

        $this->assertTrue($value);
    }

    public function testCheckPidFalse(): void
    {
        // arrange

        $webservice = new NemidWebservice(true);

        // act

        $value = $webservice->checkPid('2504050258', '9208-2002-2-193823564961');

        // assert

        $this->assertFalse($value);
    }

    /**
     * 1. Download Medarbejdercertifikat med CPR (1802602810) certificate
     * 2. Use the subject and CPR (1802602810) for tests
     *
     * @link https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/NemID-tjenesteudbyderpakken/Pages/OCES-II-certifikat-eksempler.aspx
     * @throws \SoapFault
     */
    public function testCheckRid(): void
    {
        // arrange

        $webservice = new NemidWebservice(true);

        // act

        $value = $webservice->checkRid('1802602810', 'CVR:30808460-RID:42634739');

        // assert

        $this->assertTrue($value);
    }


    public function testCheckRidFalse(): void
    {
        // arrange

        $webservice = new NemidWebservice(true);

        // act

        $value = $webservice->checkRid('1802602811', 'CVR:30808460-RID:42634739');

        // assert

        $this->assertFalse($value);
    }
}
