<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Models;

use Rentdesk\Nemid\Exceptions\InvalidCertificateException;
use Sop\X509\Certificate\Certificate;

class MOCESCertificate extends OCESCertificate
{
    public const SERIAL_NUMBER_PATTERN = '/^CVR:(\d+)-RID:(\d+)$/';

    protected string $cvr;

    protected string $rid;

    public function __construct(Certificate $certificate)
    {
        parent::__construct($certificate);

        $matches = [];

        if (!preg_match(self::SERIAL_NUMBER_PATTERN, $this->getNemidSerialNumber(), $matches)) {
            throw new InvalidCertificateException(__('nemid::errors.invalid_moces_certificate'));
        }

        $this->cvr = $matches[1];

        $this->rid = $matches[2];
    }

    /**
     * Get an organisation number (CVR)
     */
    public function getCvr(): string
    {
        return $this->cvr;
    }

    /**
     * Get a RID number.
     *
     * Resource Identification Number constitutes a unique identification of the signature holder behind
     * an employee signature.
     */
    public function getRid(): string
    {
        return $this->rid;
    }

    public function getOrganization(): string
    {
        return $this->getSubject('o');
    }
}
