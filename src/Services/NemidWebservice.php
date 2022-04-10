<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Services;

use SoapClient;
use SoapFault;

class NemidWebservice
{
    protected string $pidServiceEndpoint;

    protected string $ridServiceEndpoint;

    public function __construct(bool $debug = true)
    {
        if ($debug) {
            $this->pidServiceEndpoint = 'https://pidws.pp.certifikat.dk/pid_serviceprovider_server/pidws';
            $this->ridServiceEndpoint = 'https://ws-erhverv.pp.certifikat.dk/rid_serviceprovider_server/services/HandleSundhedsportalWSPort';
        } else {
            $this->pidServiceEndpoint = 'https://pidws.certifikat.dk/pid_serviceprovider_server/pidws';
            $this->ridServiceEndpoint = 'https://ws-erhverv.certifikat.dk/rid_serviceprovider_server/services/HandleSundhedsportalWSPort';
        }
    }

    /**
     * Error codes:
     *
     * O = OK (”OK', 'OK')
     * 1 = NO_MATCH ('CPR svarer ikke til PID', 'CPR does not match PID')
     * 2 = NO_PID ('PID eksisterer ikke', 'PID doesn't exist")
     * 4 = NO_PID_IN_CERTIFICATE ("PID kunne ikke findes i certifikatet", "No PID in certificate")
     * 8 = NOT_AUTHORIZED_FOR_CPR_LOOKUP ("Der er ikke rettighed til at foretage CPR opslag", "Caller not authorized
     * for CPR lookup")
     * 16 = BRUTE_FORCE_ATTEMPT_DETECTED ("Forsøg på systematisk søgning på CPR", "Brute force attempt detected")
     * 17 = NOT_AUTHORIZED_FOR_SERVICE_LOOKUP ( "Der er ikke rettighed til at foretage opslag på service", "Caller not
     * authorized for service lookup")
     * 4096 = NOT_PID_SERVICE_REQUEST ("Modtaget message ikke pidCprRequest eller pidCprServerStatus", "Non request XML
     * received")
     * 8192 = XML_PARSE_ERROR ("XML pakke kan ikke parses", "Non parsable XML received")
     * 8193 = XML_VERSION_NOT_SUPPORTED ("Version er ikke understøttet", "Version not supported")
     * 8194 = PID_DOES_NOT_MATCH_BASE64_CERTIFICATE ("PID stemmer med ikke med certifikat", "PID does not match
     * certifikat")
     * 8195 = MISSING_CLIENT_CERT ("Klient certifikat ikke præsenteret", "No client certificate presented")
     * 16384 = INTERNAL_ERROR ("Intern DanID fejl", "Internal DanID error")
     *
     * @link https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/implementering/Pages/FAQ-for-udviklere.aspx
     *
     * @param string $cpr CPR ex. 2504050258  (user manual input)
     * @param string $pid PID ex. 9208-2002-2-193823564960 - could be taken from the POCES certificate
     *
     * @return bool
     * @throws SoapFault
     */
    public function checkPid(string $cpr, string $pid): bool
    {
        $soapClient = new SoapClient(
            $this->pidServiceEndpoint . '?WSDL',
            [
                'local_cert' => NemidConfig::getServiceProviderCertKeyPath()
            ]
        );

        $response = $soapClient->pid(
            [
                'pIDRequests' => [
                    [
                        'serviceId' => NemidConfig::getServiceProviderID(),
                        'CPR' => $cpr,
                        'PID' => $pid
                    ]
                ]
            ]
        );

        return $response->result->PIDReply->statusCode === '0';
    }

    /**
     * @param string $cpr CPR ex. 1802602810 (user manual input)
     * @param string $nemidSerialNumber ex. CVR:30808460-RID:42634739 - could be taken from the MOCES certificate
     * @throws SoapFault
     * @link https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/NemID-tjenesteudbyderpakken/Pages/OCES-II-certifikat-eksempler.aspx
     */
    public function checkRid(string $cpr, string $nemidSerialNumber)
    {
        $soapClient = new SoapClient(
            $this->ridServiceEndpoint . '?WSDL',
            [
                'local_cert' => NemidConfig::getServiceProviderCertKeyPath()
            ]
        );

        return (bool)$soapClient->matchCPR($nemidSerialNumber, $cpr);
    }
}
