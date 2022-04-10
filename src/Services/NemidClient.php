<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Services;

use OpenSSLAsymmetricKey;
use RuntimeException;

/**
 * See NemID Integration - OCES.pdf 3.2 Parameter integrity
 */
abstract class NemidClient
{
    protected OpenSSLAsymmetricKey $serviceProviderKey;

    public function __construct(
        string $serviceProviderKey,
        protected string $serviceProviderCertificate,
        protected string $requestIssuer,
        protected string $language,
        protected bool $debug = false
    ) {
        $key = openssl_pkey_get_private($serviceProviderKey);

        if ($key === false) {
            throw new RuntimeException(__('nemid::errors.bad_sp_key'));
        }

        $this->serviceProviderKey = $key;
    }

    public static function trimPem(string $source): string
    {
        return preg_replace('/(-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s)/', '', $source);
    }

    protected function normalize(array $data): string
    {
        // The parameters are sorted alphabetically by name.
        // The sorting is case-insensitive.

        ksort($data);

        // Each parameter is concatenated to the result string as an
        // alternating sequence of name and value

        return utf8_encode(
            collect($data)->map(
                function ($key, $value) {
                    return $value . $key;
                }
            )->implode('')
        );
    }

    protected function digest(string $data): string
    {
        return hash('sha256', $data, true);
    }

    protected function sign(string $data): string
    {
        $signature = '';

        $result = openssl_sign($data, $signature, $this->serviceProviderKey, 'sha256WithRSAEncryption');

        if (!$result) {
            throw new RuntimeException(__('nemid::errors.signing_failure'));
        }

        return $signature;
    }
}
