<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Models;

use Carbon\Carbon;
use Sop\CryptoEncoding\PEM;
use Sop\X509\Certificate\Certificate;
use Sop\X509\GeneralName\GeneralName;

class X509Certificate
{
    public function __construct(protected Certificate $certificate)
    {
    }

    public static function fromString(string $string): static
    {
        return new static(Certificate::fromPEM(PEM::fromString($string)));
    }

    public function hasSubject(string $subject): bool
    {
        return $this->certificate->tbsCertificate()->subject()->countOfType($subject) > 0;
    }

    public function getSubject(string $subject): string
    {
        return $this->certificate->tbsCertificate()->subject()->firstValueOf($subject)->stringValue();
    }

    public function getCommonName(): string
    {
        return $this->certificate->tbsCertificate()->subject()->firstValueOf('cn')->stringValue();
    }

    public function getValidNotBefore(): Carbon
    {
        return Carbon::parse($this->certificate->tbsCertificate()->validity()->notBefore()->dateTime());
    }

    public function getValidNotAfter(): Carbon
    {
        return Carbon::parse($this->certificate->tbsCertificate()->validity()->notAfter()->dateTime());
    }

    public function getCRLDistributionPoints(): array
    {
        $result = [];

        if ($this->certificate->tbsCertificate()->extensions()->hasCRLDistributionPoints()) {
            $points = $this->certificate->tbsCertificate()->extensions()->crlDistributionPoints()->distributionPoints();

            foreach ($points as $point) {
                if ($point->hasFullName() && $point->fullName()->names()->has(GeneralName::TAG_URI)) {
                    $result[] = $point->fullName()->names()->firstURI();
                }
            }
        }

        return $result;
    }

    public function getSerialNumber(): string
    {
        return $this->certificate->tbsCertificate()->serialNumber();
    }

    public function getIssuerCommonName(): string
    {
        return $this->certificate->tbsCertificate()->issuer()->firstValueOf('cn')->stringValue();
    }
}
