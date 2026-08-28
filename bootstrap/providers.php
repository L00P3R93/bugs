<?php

use App\Providers\AppServiceProvider;
use App\Providers\BrevoMailServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    BrevoMailServiceProvider::class,
    AdminPanelProvider::class,
    FortifyServiceProvider::class,
];
