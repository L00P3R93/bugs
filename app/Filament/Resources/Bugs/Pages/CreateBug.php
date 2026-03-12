<?php

namespace App\Filament\Resources\Bugs\Pages;

use App\Filament\Resources\Bugs\BugResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBug extends CreateRecord
{
    protected static string $resource = BugResource::class;
}
