<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use RuntimeException;

class NemidConfig
{
    public static function getTrustedRoot(): string
    {
        return File::get(static::getTrustedRootPath());
    }

    public static function getTrustedRootPath(): string
    {
        return Config::get('nemid.certificates.root');
    }

    public static function getIntermediates(): array
    {
        return array_map(static fn($path) => File::get($path), Config::get('nemid.certificates.intermediate'));
    }

    public static function getIntermediatePath(string $cn): string
    {
        $certificates = Config::get('nemid.certificates.intermediate');

        if (isset($certificates[$cn])) {
            return $certificates[$cn];
        }

        throw new RuntimeException(__('nemid::errors.intermediate_certificate_not_found', ['cn' => $cn]));
    }

    public static function getCrlPath(string $uri): string
    {
        $crls = Config::get('nemid.certificates.crl');

        if (isset($crls[$uri])) {
            return $crls[$uri];
        }

        throw new RuntimeException(__('nemid::errors.crl_not_found', ['uri' => $uri]));
    }

    public static function getOCSPResponder(string $cn): string
    {
        $ocspResponders = Config::get('nemid.ocsp_responders');

        if (isset($ocspResponders[$cn])) {
            return $ocspResponders[$cn];
        }

        throw new RuntimeException(__('nemid::errors.ocsp_responder_not_found', ['cn' => $cn]));
    }

    public static function getServiceProviderKeyPath(): string
    {
        return Config::get('nemid.sp_key');
    }

    public static function getServiceProviderCertPath(): string
    {
        return Config::get('nemid.sp_cert');
    }

    /**
     * Concatenated PEM file with both certificate and key for some HTTP (SOAP) clients
     *
     * @return string
     */
    public static function getServiceProviderCertKeyPath(): string
    {
        return Config::get('nemid.sp_cert_key');
    }

    public static function getServiceProviderID(): string
    {
        return Config::get('nemid.sp_id');
    }
}
