<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Services;

use Carbon\Carbon;

class JavaScriptClient extends NemidClient
{
    public function getLoginConfig(): array
    {
        $parameters = [
            'CLIENTFLOW' => 'OCESLOGIN2',
            'DO_NOT_SHOW_CANCEL' => 'TRUE',
            'LANGUAGE' => $this->language,
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
        $host = $this->debug ? 'appletk.danid.dk' : 'applet.danid.dk';

        return "https://$host/launcher/std/" . Carbon::now()->timestamp;
    }
}
