<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Models;

/**
 * Class OCESCertificate
 *
 * For use by public service providers, a standard for certificates called Public
 * Certificates for Electronic Service (In Danish: Offentlige Certifikater til Elektroniske Service = OCES) has been
 * established. OCES is available in different versions for use by individuals and businesses:
 *
 * OCES for individuals is called “POCES”
 * OCES for employees is called “MOCES”
 * OCES for businesses is called “VOCES” - used by banks
 * OCES for IT systems is called “FOCES”
 */
abstract class OCESCertificate extends X509Certificate
{
    public function getNemidSerialNumber(): string
    {
        return $this->getSubject('serialNumber');
    }

    public function getIdentity(): string
    {
        return $this->getCommonName();
    }
}
