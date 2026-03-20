<?php

namespace App\Facades;

use App\Services\KadiApiService;
use Illuminate\Support\Facades\Facade;

/**
 * @see KadiApiService
 */
class KadiApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return KadiApiService::class;
    }
}
