<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Models;

use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Rentdesk\Nemid\Exceptions\InvalidCertificateException;
use Rentdesk\Nemid\Exceptions\InvalidSignatureException;
use Rentdesk\Nemid\Services\NemidConfig;
use Rentdesk\Nemid\Services\NemidRevocationChecker;
use Sop\CryptoEncoding\PEM;
use Sop\X509\Certificate\Certificate;
use Sop\X509\Certificate\CertificateBundle;
use Sop\X509\CertificationPath\CertificationPath;
use Sop\X509\CertificationPath\Exception\PathValidationException;
use Sop\X509\CertificationPath\PathValidation\PathValidationConfig;

class XMLDSig
{
    protected DOMDocument $DOMDocument;

    protected DOMXPath $DOMXPath;

    protected Certificate $endEntityCertificate;

    protected Certificate $intermediateCertificate;

    protected Certificate $rootCertificate;

    protected ?Carbon $timestamp;

    /**
     * The ds:KeyInfo element contains the signing certificate chain.
     * The signing certificate holds the public key which will decrypt the SignatureValue.
     * @see https://www.lss-for-nemid.dk/docs/LSS%20Technical%20Specification%2011.pdf
     *
     * Although the document says the sequence MUST be ordered, it is not.
     */
    public function __construct(string $xmlSource)
    {
        $this->DOMDocument = new DOMDocument();
        $this->DOMDocument->loadXML($xmlSource);

        $this->DOMXPath = new DomXPath($this->DOMDocument);
        $this->DOMXPath->registerNamespace('openoces', 'http://www.openoces.org/2006/07/signature#');
        $this->DOMXPath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        $nodeList = $this->DOMXPath->query(
            '/openoces:signature/ds:Signature/ds:KeyInfo/ds:X509Data/ds:X509Certificate'
        );

        if ($nodeList->count() !== 3) {
            throw new InvalidSignatureException(__('nemid::errors.invalid_certificate_chain_count'));
        }

        // Although the documentation says the certificate chain is strict ordered, it is not in fact

        $chain = collect($nodeList->getIterator())->map(
            fn($source) => self::certificateFromRawPEM($source->nodeValue)
        );

        // Looking for root primary CA certificate

        $trustedRoot = Certificate::fromPEM(Pem::fromString(NemidConfig::getTrustedRoot()));

        $rootCertificate = collect($chain)->first(
            function (Certificate $certificate) use ($trustedRoot) {
                return $certificate->equals($trustedRoot);
            }
        );

        if ($rootCertificate === null) {
            throw new InvalidSignatureException(__('nemid::errors.root_certificate_not_trusted'));
        }

        // Looking for issuing intermediate certificate

        $trustedIntermediates = collect(NemidConfig::getIntermediates())->map(
            fn($source) => Certificate::fromPEM(Pem::fromString($source))
        );

        $intermediateCertificate = $chain->first(
            function (Certificate $certificate) use ($trustedIntermediates) {
                return $trustedIntermediates->filter(fn($issuer) => $certificate->equals($issuer))->isNotEmpty();
            }
        );

        if ($intermediateCertificate === null) {
            throw new InvalidSignatureException(__('nemid::errors.intermediate_certificate_invalid'));
        }

        // Looking for end-entity leaf certificate

        $endEntityCertificate = $chain->first(
            function (Certificate $certificate) use ($rootCertificate, $intermediateCertificate) {
                return !$certificate->equals($rootCertificate) && !$certificate->equals($intermediateCertificate);
            }
        );

        if ($endEntityCertificate === null) {
            throw new InvalidSignatureException(__('nemid::errors.leaf_certificate_not_found'));
        }

        $this->rootCertificate = $rootCertificate;
        $this->intermediateCertificate = $intermediateCertificate;
        $this->endEntityCertificate = $endEntityCertificate;

        // Signature timestamp

        $timestampValueNodeList = $this->DOMXPath->evaluate(".//openoces:Name[text()='TimeStamp']//../openoces:Value");

        if ($timestampValueNodeList->count() === 0) {
            // MOCES signatures may be shipped without a timestamp
            $this->timestamp = null;

            return;
        }

        $timestampNode = $timestampValueNodeList->item(0);
        $timestampEncoding = $timestampNode->getAttribute('Encoding');

        // Timestamp could be in 2 encoding

        if ($timestampEncoding !== 'xml' && $timestampEncoding !== 'base64') {
            throw new InvalidSignatureException(__('nemid::errors.unknown_timestamp_encoding'));
        }

        if ($timestampEncoding === 'xml') {
            $this->timestamp = Carbon::createFromFormat('Y-m-d H:i:sO', $timestampNode->nodeValue);
        }

        if ($timestampEncoding === 'base64') {
            $this->timestamp = Carbon::createFromFormat('Y-m-d H:i:sO', base64_decode($timestampNode->nodeValue));
        }
    }

    protected static function certificateFromRawPEM(string $source): Certificate
    {
        $pem = "-----BEGIN CERTIFICATE-----\n" . $source . "\n-----END CERTIFICATE-----";

        return Certificate::fromPEM(PEM::fromString($pem));
    }

    public static function fromBase64(string $encodedStr): self
    {
        return new static(base64_decode($encodedStr));
    }

    public function validate(): bool
    {
        // 1. Validate the signature

        $context = $this->DOMXPath->query('/openoces:signature/ds:Signature')->item(0);

        $signedObjectElement = $this->DOMXPath->query('ds:Object[@Id="ToBeSigned"]', $context)->item(0);

        if ($signedObjectElement === null) {
            throw new InvalidSignatureException(__('nemid::errors.no_signed_object'));
        }

        $signedObject = $signedObjectElement->C14N();

        $digestValueElement = $this->DOMXPath->query('ds:SignedInfo/ds:Reference/ds:DigestValue', $context)
            ->item(0);

        if ($digestValueElement === null) {
            throw new InvalidSignatureException(__('nemid::errors.no_signature_digest'));
        }

        $digestValue = base64_decode($digestValueElement->textContent);

        $signedInfoElement = $this->DOMXPath->query('ds:SignedInfo', $context)->item(0);

        if ($signedInfoElement === null) {
            throw new InvalidSignatureException(__('nemid::errors.no_signed_info'));
        }

        $signedInfo = $signedInfoElement->C14N();

        $signatureValueElement = $this->DOMXPath->query('ds:SignatureValue', $context)->item(0);

        if ($signatureValueElement === null) {
            throw new InvalidSignatureException(__('nemid::errors.no_signature_value'));
        }

        $signatureValue = base64_decode($signatureValueElement->textContent);

        if (hash('sha256', $signedObject, true) !== $digestValue) {
            throw new InvalidSignatureException(__('nemid::errors.invalid_digest'));
        }

        $publicKey = openssl_get_publickey($this->endEntityCertificate->toPEM());

        if ($publicKey === false) {
            throw new InvalidSignatureException(__('nemid::errors.no_public_key'));
        }

        if (openssl_verify($signedInfo, $signatureValue, $publicKey, 'sha256WithRSAEncryption') === false) {
            throw new InvalidSignatureException(__('nemid::errors.signature_verification_failed'));
        }

        // 2. Validate the certificate chain

        $certificatePath = CertificationPath::fromTrustAnchorToTarget(
            $this->rootCertificate,
            $this->endEntityCertificate,
            CertificateBundle::fromPEMs($this->intermediateCertificate->toPEM())
        );

        // Any policy
        // Max chain length = 3
        // Validate expiration date

        $config = PathValidationConfig::defaultConfig();

        try {
            $certificatePath->validate($config);
        } catch (PathValidationException $exception) {
            throw new InvalidCertificateException($exception->getMessage());
        }

        // 3. Validate the leaf certificate using CRL and/or OCSP if enabled

        /** @var NemidRevocationChecker $nemidRevocationChecker */
        $nemidRevocationChecker = app()->make(NemidRevocationChecker::class);

        if ($nemidRevocationChecker->isRevoked($this->getLeafCertificate())) {
            throw new InvalidCertificateException(__('nemid::leaf_certificate_revoked'));
        }

        return true;
    }

    public function getTimestamp(): ?Carbon
    {
        return $this->timestamp;
    }

    /**
     * A "leaf certificate" is what is more commonly known as end-entity certificate. Certificates come in chains,
     * starting with the root CA, each certificate being the CA which issued (signed) the next one. The last
     * certificate is the non-CA certificate which contains the public key you actually want to use - index 2 in OCES.
     *
     * @return POCESCertificate|MOCESCertificate|VOCESCertificate
     */
    public function getLeafCertificate(): POCESCertificate|MOCESCertificate|VOCESCertificate
    {
        $x509 = new X509Certificate($this->endEntityCertificate);

        $serialNumber = $x509->getSubject('serialNumber');

        if (preg_match(POCESCertificate::SERIAL_NUMBER_PATTERN, $serialNumber)) {
            return new POCESCertificate($this->endEntityCertificate);
        }

        if (preg_match(MOCESCertificate::SERIAL_NUMBER_PATTERN, $serialNumber)) {
            return new MOCESCertificate($this->endEntityCertificate);
        }

        if (preg_match(VOCESCertificate::SERIAL_NUMBER_PATTERN, $serialNumber)) {
            return new VOCESCertificate($this->endEntityCertificate);
        }

        throw new InvalidCertificateException(__('nemid::errors.unknown_certificate_type'));
    }
}
