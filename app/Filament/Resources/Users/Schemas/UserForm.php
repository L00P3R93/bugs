<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class UserForm
{
    /**
     * Format phone number for password generation (254 -> 0)
     */
    private static function formatPhoneForPassword(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $phone = trim($phone);
        // If phone starts with 254, replace with 0
        if (str_starts_with($phone, '254')) {
            return '0'.substr($phone, 3);
        }

        return $phone;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('Full name')
                    ->prefixIcon(Heroicon::User)
                    ->prefixIconColor('primary')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                        // Generate password when both name and phone are filled
                        $name = trim($state);
                        $phone = self::formatPhoneForPassword($get('phone'));
                        if (filled($name) && filled($phone)) {
                            $firstName = Str::of($name)->before(' ')->lower()->ucfirst();
                            $generatedPassword = "{$firstName}@{$phone}";
                            $set('password', $generatedPassword);
                            $set('password_confirmation', $generatedPassword);
                        }
                    })
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->autocomplete(false)
                    ->unique(ignoreRecord: true)
                    ->prefixIcon(Heroicon::AtSymbol)
                    ->prefixIconColor('primary')
                    ->validationMessages([
                        'email' => 'Invalid email address.',
                        'required' => 'Email address is required.',
                        'unique' => 'This email address is already in use.',
                    ])
                    ->required(),
                TextInput::make('phone')
                    ->label('Phone number')
                    ->tel()
                    ->telRegex('/^(?:\+254|254|0)(7\d{8}|1\d{8})$/')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (callable $set, $state, callable $get) {
                        // Generate password when both name and phone are filled
                        $name = $get('name');
                        $phone = self::formatPhoneForPassword(trim($state));
                        if (filled($name) && filled($phone)) {
                            $firstName = Str::of($name)->before(' ')->lower()->ucfirst();
                            $generatedPassword = "{$firstName}@{$phone}";
                            $set('password', $generatedPassword);
                            $set('password_confirmation', $generatedPassword);
                        }
                    })
                    ->unique(ignoreRecord: true)
                    ->prefixIcon(Heroicon::Phone)
                    ->prefixIconColor('primary')
                    ->validationMessages([
                        'unique' => 'This phone number is already in use.',
                        'required' => 'Phone number is required.',
                        'regex' => 'Invalid phone number.',
                    ])
                    ->required(),
                Select::make('status')
                    ->label('User Status')
                    ->options(UserStatus::class)
                    ->default('active')
                    ->prefixIcon(Heroicon::OutlinedExclamationCircle)
                    ->prefixIconColor('primary')
                    ->native(false)
                    ->required(),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->label('User Roles')
                    ->prefixIcon(Heroicon::OutlinedShieldExclamation)
                    ->prefixIconColor('primary')
                    ->preload()
                    ->multiple()
                    ->native(false)
                    // ->disabled(fn (?User $record) => $record !== null)
                    // ->createOptionAction(fn (Action $action) => $action->visible(auth()->user()->can('manage roles')))
                    /*->createOptionForm(function () {
                        return RoleForm::configure(Schema::wrap())->getComponents();
                    })*/
                    ->searchable()
                    ->required(),

                Section::make('Password')->schema([
                    TextInput::make('password')
                        ->label('Password')
                        ->prefixIcon(Heroicon::OutlinedLockClosed)
                        ->prefixIconColor('primary')
                        ->password()
                        ->revealable()
                        ->required(fn (string $context) => $context === 'create')
                        ->dehydrated(fn ($state) => filled($state)) // only send if filled
                        ->default(fn (callable $get) => function () use ($get) {
                            $name = $get('name');
                            $phone = self::formatPhoneForPassword($get('phone'));
                            if (filled($name) && filled($phone)) {
                                $firstName = Str::of($name)->before(' ')->lower()->ucfirst();

                                return "{$firstName}@{$phone}";
                            }

                            return null;
                        })
                        ->rules([
                            'confirmed',
                            'regex:/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/',
                        ])
                        ->validationMessages([
                            'regex' => 'Password must be at least 8 characters long, contain at least one uppercase letter, and one special symbol.',
                            'confirmed' => 'Password confirmation does not match.',
                            'required' => 'Password is required.',
                        ]),
                    TextInput::make('password_confirmation')
                        ->label('Confirm Password')
                        ->prefixIcon(Heroicon::OutlinedLockClosed)
                        ->prefixIconColor('primary')
                        ->password()
                        ->revealable()
                        ->dehydrated(false) // don't send to DB
                        ->required(fn (string $context) => $context === 'create'),
                ])->columnSpanFull()->hidden(fn (?User $record) => $record !== null),
            ])->columns(2)->columnSpan(['lg' => fn (?User $record) => $record === null ? 3 : 2]),

            Section::make()->schema([
                TextEntry::make('created_at')->state(fn (User $record): ?string => $record->created_at?->diffForHumans()),
                TextEntry::make('updated_at')->label('Last modified at')->state(fn (User $record): ?string => $record->updated_at?->diffForHumans()),
            ])->columnSpan(['lg' => 1])->hidden(fn (?User $record) => $record === null),

        ])->columns(3);
    }
}
