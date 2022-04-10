<?php

return [
    /**
     * Service Provider ID
     *
     * For test SPID see
     * @ling https://www.nets.eu/dk-da/l%C3%B8sninger/nemid/nemid-tjenesteudbyder/supplerende-tjenester/pid-rid-cpr-tjenester/Pages/kom-godt-i-gang-med-at-teste-pid-cpr-tjenesten.aspx
     */
    'sp_id' => env('NEMID_SP_ID'),

    /**
     * Service Provider VOCES certificate issued by Nets-DanID certificate authority in PEM format.
     *
     * @example production.cert.pem
     */

    'sp_cert' => env('NEMID_SP_CERT'),

    /**
     * Path to Service Provider PEM representation of the private key used for signing.
     *
     * @example production.key.pem
     */
    'sp_key' => env('NEMID_SP_KEY'),

    /**
     * Path to Service Provider concatenated PEM representation of both private key and certificate for using in
     * NemID Webservice requests.
     */
    'sp_cert_key' => env('NEMID_SP_CERT_KEY'),

    /**
     * CRL certificate revocation status check using the local cache.
     * Make sure you have set up CRL files path and update them using the cron job.
     */
    'crl_check_enabled' => env('NEMID_CRL_CHECK_ENABLED', false),

    /**
     * OCSP certificate revocation status check using HTTP request.
     * Make sure your IP address can access NemID responders.
     *
     * @link https://www.nets.eu/dk-da/l%C3%B8sninger/nemid/nemid-tjenesteudbyder/supplerende-tjenester/ocsp
     * @link https://www.nets.eu/dk-da/l%C3%B8sninger/nemid/nemid-tjenesteudbyder/supplerende-tjenester/ocsp/Documents/Specifikationsdokument%20for%20OCSP.pdf
     */
    'ocsp_check_enabled' => env('NEMID_OCSP_CHECK_ENABLED', false),

    /**
     *
     * [CN => URL]
     */
    'ocsp_responders' => [

        // Production

        'TRUST2408 OCES CA III' => 'http://ocsp.ica03.trust2408.com/responder',
        'TRUST2408 OCES CA IV' => 'http://ocsp.ica04.trust2408.com/responder',
        'TRUST2408 OCES CA V' => 'http://ocsp.ica05.trust2408.com/responder',

        // Test

        'TRUST2408 Systemtest VIII CA' => 'http://ocsp.systemtest8.trust2408.com/responder',
        'TRUST2408 Systemtest XXII CA' => 'http://ocsp.systemtest22.trust2408.com/responder',
        'TRUST2408 Systemtest XXXIV CA' => 'http://ocsp.systemtest34.trust2408.com/responder',
    ],

    /**
     * Path to the CA certificates to validate CA as OCES throughout the
     * whole certificate chain to the root certificate.
     *
     * @link https://www.nets.eu/dk-da/kundeservice/nemid-tjenesteudbyder/The-NemID-service-provider-package/Pages/news.aspx
     */
    'certificates' => [

        /**
         * Root (Primary) Certificate Authority
         * TRUST2408 OCES Primary CA for production
         * TRUST2408 Systemtest VII Primary CA for development
         */
        'root' => 'certificates/TRUST2408-Systemtest-VII-Primary-CA.cert.pem',

        /**
         * Issuing Certificate Authority
         *
         * [CN => Path]
         */
        'intermediate' => [

            // Production

            'TRUST2408 OCES CA III' => 'certificates/TRUST2408-OCES-CA-III.cert.pem',
            'TRUST2408 OCES CA IV' => 'certificates/TRUST2408-OCES-CA-IV.cert.pem',
            'TRUST2408 OCES CA V' => 'certificates/TRUST2408-OCES-CA-V.cert.pem',

            // Test

            'TRUST2408 Systemtest VIII CA' => 'certificates/TRUST2408-Systemtest-VIII-CA.cert.pem',
            'TRUST2408 Systemtest XXII CA' => 'certificates/TRUST2408-Systemtest-XXII-CA.cert.pem',
            'TRUST2408 Systemtest XXXIV CA' => 'certificates/TRUST2408-Systemtest-XXXIV-CA.cer.pem',
        ],

        /**
         * In case of a block, Nets DanID puts your certificate number on a block list.
         * Download the CRLs manually and create [URI => Path] entries
         *
         * @link https://www.nets.eu/dk-da/kundeservice/NemID-Til-Private/Pages/Repository.aspx
         * @see \Rentdesk\Nemid\Services\NemidService::isRevoked()
         */
        'crl' => [

            // Production

            'http://crl.ica05.trust2408.com/ica05.crl' => 'certificates/ica05.crl',
            'http://crl.ica04.trust2408.com/ica04.crl' => 'certificates/ica04.crl',
            'http://crl.ica03.trust2408.com/ica03.crl' => 'certificates/ica03.crl',
            'http://crl.ica02.trust2408.com/ica02.crl' => 'certificates/ica02.crl',
            'http://crl.oces-issuing01.trust2408.com/ica011.crl' => 'certificates/ica011.crl',

            // Test

            'http://crl.systemtest8.trust2408.com/systemtest8.crl' => 'certificates/systemtest8.crl',
            'http://crl.systemtest34.trust2408.com/systemtest34.crl' => 'certificates/systemtest34.crl',
            'http://crl.systemtest22.trust2408.com/systemtest221.crl' => 'certificates/systemtest221.crl'
        ]
    ],

    /**
     * Route middleware
     */
    'middleware' => []
];
