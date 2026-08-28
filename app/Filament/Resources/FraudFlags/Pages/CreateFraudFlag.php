<?php

namespace App\Filament\Resources\FraudFlags\Pages;

use App\Filament\Resources\FraudFlags\FraudFlagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFraudFlag extends CreateRecord
{
    protected static string $resource = FraudFlagResource::class;
}
