<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Models;

use Rentdesk\Nemid\Exceptions\InvalidCertificateException;
use Sop\X509\Certificate\Certificate;

class POCESCertificate extends OCESCertificate
{
    protected const TOO_YOUNG_OU = 'Ung mellem 15 og 18 - Kan som udgangspunkt ikke lave juridisk bindende aftaler';

    public const SERIAL_NUMBER_PATTERN = '/^PID:([\d\-]*)$/';

    protected string $pid;

    public function __construct(Certificate $certificate)
    {
        parent::__construct($certificate);

        $matches = [];

        if (!preg_match(self::SERIAL_NUMBER_PATTERN, $this->getNemidSerialNumber(), $matches)) {
            throw new InvalidCertificateException(__('nemid::errors.invalid_poces_certificate'));
        }

        $this->pid = $matches[1];
    }

    /**
     * Get Personal ID
     */
    public function getPid(): string
    {
        return $this->pid;
    }

    /**
     * Young people aged 15-18 have limited certificates
     *
     * @return bool
     */
    public function isYoung(): bool
    {
        return $this->hasSubject('ou') && $this->getSubject('ou') === self::TOO_YOUNG_OU;
    }
}
