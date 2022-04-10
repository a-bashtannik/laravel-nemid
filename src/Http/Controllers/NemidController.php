<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Rentdesk\Nemid\Facades\Nemid;

class NemidController extends Controller
{
    public function javascript(): JsonResponse
    {
        return response()->json(Nemid::javaScriptClient()->getLoginConfig());
    }

    public function codefile(): JsonResponse
    {
        return response()->json(Nemid::codeFileClient()->getLoginConfig());
    }
}
