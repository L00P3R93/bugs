<?php

namespace App\Providers\Filament;

use App\Filament\Pages\EditProfilePage;
use App\Http\Middleware\EnsureEmailVerified;
use Filament\Actions\Action;
use Filament\Enums\ThemeMode;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->homeUrl('/admin')
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->userMenuItems([
                'profile' => fn (Action $action) => $action
                    ->label('Edit profile')
                    ->icon('heroicon-s-user')
                    ->url(fn (): string => EditProfilePage::getUrl()),
            ])
            ->defaultThemeMode(ThemeMode::Dark)
            ->darkMode()
            ->colors([
                'primary' => Color::Amber,
                'secondary' => Color::Gray,
                'info' => Color::Cyan,
                'success' => Color::Green,
                'warning' => Color::Yellow,
                'danger' => Color::Red,
                'purple' => Color::Purple,
                'orange' => Color::Orange,
                'blue' => Color::Blue,
                'pink' => Color::Pink,
                'teal' => Color::Teal,
                'yellow' => Color::Yellow,
                'red' => Color::Red,
                'green' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->navigationGroups([
                'Wallets & Transactions',
                'Bug Management',
                'User Management',
                'System Management',
            ])
            ->navigationItems([
                NavigationItem::make('System Logs')
                    ->url('/log-viewer')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->group('System Management')
                    ->sort(50)
                    ->visible(fn () => auth()->user()?->isSuperAdmin()),
            ])
            ->databaseNotifications()
            ->unsavedChangesAlerts()
            ->spa()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureEmailVerified::class,
            ]);
    }
}
