<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Services;

use Rentdesk\Nemid\Models\OCESCertificate;
use RuntimeException;
use Symfony\Component\Process\Process;

class NemidRevocationChecker
{
    public function __construct(protected $crlCheckEnabled, protected $ocspCheckEnabled)
    {
    }

    public function isRevoked(OCESCertificate $certificate): bool
    {
        $isRevoked = false;

        if (count($certificate->getCRLDistributionPoints()) === 0) {
            throw new RuntimeException(__('nemid::errors.no_crl_distribution_points'));
        }

        if ($this->crlCheckEnabled) {
            $isRevoked = self::isRevokedCRL($certificate);
        }

        if ($isRevoked === false && $this->ocspCheckEnabled) {
            $isRevoked = self::isRevokedOCSP($certificate);
        }

        return $isRevoked;
    }

    public static function isRevokedCRL(OCESCertificate $certificate): bool
    {
        $isRevoked = false;

        $hexSerialStr = dechex((int)$certificate->getSerialNumber());

        $crlPath = NemidConfig::getCrlPath($certificate->getCRLDistributionPoints()[0]);

        $opensslProcess = new Process(
            [
                'openssl',
                'crl',
                '-inform=DER',
                '-noout',
                '-text',
                "-in=$crlPath"
            ]
        );

        $opensslProcess->run(
            function ($type, $buffer) use ($opensslProcess, $hexSerialStr, &$isRevoked) {
                if ($type === Process::ERR) {
                    throw new RuntimeException($buffer);
                }

                if ($type === Process::OUT && preg_match("/Serial Number: $hexSerialStr/im", $buffer)) {
                    $isRevoked = true;
                    $opensslProcess->stop();
                }
            }
        );

        return $isRevoked;
    }

    public static function isRevokedOCSP(OCESCertificate $certificate): bool
    {
        $issuerCN = $certificate->getIssuerCommonName();
        $intermediatePath = NemidConfig::getIntermediatePath($issuerCN);

        $rootPath = NemidConfig::getTrustedRootPath();
        $responderUrl = NemidConfig::getOCSPResponder($issuerCN);

        $serial = "0x" . dechex((int)$certificate->getSerialNumber());

        $opensslProcess = new Process(
            [
                'openssl',
                'ocsp',
                "-issuer=$intermediatePath",
                "-serial=$serial",
                "-url=$responderUrl",
                "-CAfile=$rootPath"
            ]
        );

        $opensslProcess->run();

        return preg_match("/$serial: good/", $opensslProcess->getOutput()) !== 1;
    }
}
