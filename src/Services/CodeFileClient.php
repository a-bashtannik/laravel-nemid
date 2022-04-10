<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Services;

use Carbon\Carbon;

class CodeFileClient extends NemidClient
{
    public function getLoginConfig(): array
    {
        $parameters = [
            'CLIENTFLOW' => 'login',
            'ISSUERDNFILTER' => 'VFJVU1QyNDA4', // Hardcoded value
            'LANGUAGE' => $this->language,
            'REQUESTISSUER' => base64_encode($this->requestIssuer),
            'SP_CERT' => static::trimPem($this->serviceProviderCertificate),
            'TIMESTAMP' => base64_encode(now()->setTimezone('Europe/Copenhagen')->format('Y-m-d H:i:sO')),
        ];

        $normalized = $this->normalize($parameters);

        $parameters['PARAMS_DIGEST'] = base64_encode($this->digest($normalized));
        $parameters['DIGEST_SIGNATURE'] = base64_encode($this->sign($normalized));

        return $parameters;
    }

    public function getEndpoint(): string
    {
        $host = $this->debug ? 'codefileclient.pp.danid.dk' : 'codefileclient.danid.dk';

        return "https://$host/?t=" . Carbon::now()->timestamp;
    }
}
