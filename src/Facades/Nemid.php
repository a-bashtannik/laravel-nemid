<?php

declare(strict_types=1);

namespace Rentdesk\Nemid\Facades;

use Illuminate\Support\Facades\Facade;

class Nemid extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'nemid';
    }
}
