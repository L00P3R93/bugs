<?php

namespace App\Providers;

use App\Models\Bug;
use App\Models\User;
use App\Models\Wallet;
use App\Observers\BugObserver;
use App\Observers\UserObserver;
use App\Observers\WalletObserver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureObservers();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Model::automaticallyEagerLoadRelationships();
        Model::unguard();

        if (app()->environment('production')) {
            URL::forceHttps();
        }

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureObservers(): void
    {
        User::observe(UserObserver::class);
        Wallet::observe(WalletObserver::class);
        Bug::observe(BugObserver::class);
    }
}
