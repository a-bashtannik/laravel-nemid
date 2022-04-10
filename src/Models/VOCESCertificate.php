<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Models;

use Rentdesk\Nemid\Exceptions\InvalidCertificateException;
use Sop\X509\Certificate\Certificate;

class VOCESCertificate extends OCESCertificate
{
    public const SERIAL_NUMBER_PATTERN = '/^CVR:(\d+)-UID:(\d+)$/';

    protected string $cvr;

    protected string $uid;

    public function __construct(Certificate $certificate)
    {
        parent::__construct($certificate);

        $matches = [];

        if (!preg_match(self::SERIAL_NUMBER_PATTERN, $this->getNemidSerialNumber(), $matches)) {
            throw new InvalidCertificateException(__('nemid::errors.invalid_voces_certificate'));
        }

        $this->cvr = $matches[1];

        $this->uid = $matches[2];
    }

    public function getCvr(): string
    {
        return $this->cvr;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function getOrganization(): string
    {
        return $this->getSubject('o');
    }
}
