<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Services;

use Rentdesk\Nemid\Models\XMLDSig;

class NemidService
{
    public function __construct(
        protected string $serviceProviderId,
        protected string $serviceProviderKey,
        protected string $serviceProviderCertificate,
        protected string $appName,
        protected string $language,
        protected bool $debug = false
    ) {
    }

    public function codeFileClient(): CodeFileClient
    {
        return new CodeFileClient(
            $this->serviceProviderKey,
            $this->serviceProviderCertificate,
            $this->appName,
            $this->language,
            $this->debug
        );
    }

    public function javaScriptClient(): JavaScriptClient
    {
        return new JavaScriptClient(
            $this->serviceProviderKey,
            $this->serviceProviderCertificate,
            $this->appName,
            $this->language,
            $this->debug
        );
    }

    public function parseResponse(string $encodedString): XMLDSig
    {
        return XMLDSig::fromBase64($encodedString);
    }

    public function webservice(): NemidWebservice
    {
        return new NemidWebservice($this->debug);
    }
}
