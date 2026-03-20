<?php

namespace App\Filament\Pages;

use App\Facades\KadiApi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EditProfilePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.edit-profile-page';

    protected static ?string $title = 'Edit Profile';

    protected static ?string $navigationLabel = 'Edit Profile';

    protected static ?int $navigationSort = 100;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'linked_id' => $user->linked_id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'account_no' => $user->account_no,
            'profile_photo' => $user->getFirstMedia('avatars'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make('Kadi Account')
                            ->icon(Heroicon::OutlinedLink)
                            ->schema([
                                Section::make('Link Your Kadi Play Account')
                                    ->description('Search by name, account number, phone or email to link your Kadi Play account.')
                                    ->schema([
                                        Select::make('linked_id')
                                            ->label('Kadi Account')
                                            ->prefixIcon('hugeicons-user-search-01')
                                            ->prefixIconColor('primary')
                                            ->searchPrompt('Search by name, account number, phone or email...')
                                            ->native(false)
                                            ->loadingMessage('Searching Kadi Accounts...')
                                            ->noSearchResultsMessage('No Kadi Accounts found.')
                                            ->searchable()
                                            ->searchDebounce(500)
                                            ->getSearchResultsUsing(function (string $search): array {
                                                if (strlen($search) < 4) {
                                                    return [];
                                                }

                                                try {
                                                    $results = KadiApi::get('customers/search', ['q' => $search]);

                                                    return collect($results)
                                                        ->mapWithKeys(fn ($item) => [
                                                            $item['id'] => "Name: {$item['name']} | Account No: {$item['account_no']} | Phone No: {$item['phone_no']}",
                                                        ])
                                                        ->toArray();
                                                } catch (\Exception $e) {
                                                    return [];
                                                }
                                            })
                                            ->getOptionLabelUsing(function ($value): ?string {
                                                if (! $value) {
                                                    return null;
                                                }

                                                $cached = Cache::get('kadi_accounts');

                                                if ($cached) {
                                                    $account = $cached->firstWhere('id', (int) $value);

                                                    if ($account) {
                                                        return "Name: {$account['name']} | Account No: {$account['account_no']}";
                                                    }
                                                }

                                                return "Kadi Account #{$value}";
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Profile')
                            ->icon(Heroicon::OutlinedUser)
                            ->schema([
                                Section::make('Profile Photo')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('profile_photo')
                                            ->label('Profile Photo')
                                            ->image()
                                            ->collection('avatars')
                                            ->model(fn () => auth()->user())
                                            ->preserveFilenames()
                                            ->imageEditor()
                                            ->openable()
                                            ->downloadable()
                                            ->columnSpanFull()
                                            ->required(false)
                                            ->maxSize(10240)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif']),
                                    ])->compact(),

                                Section::make('Account Details')
                                    ->schema([
                                        TextInput::make('username')
                                            ->label('Username')
                                            ->prefixIcon(Heroicon::OutlinedUserCircle)
                                            ->prefixIconColor('primary')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('users', 'username', ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->ignore(auth()->id())),

                                        TextInput::make('name')
                                            ->label('Full Name')
                                            ->prefixIcon(Heroicon::OutlinedUser)
                                            ->prefixIconColor('primary')
                                            ->required(),

                                        TextInput::make('email')
                                            ->label('Email Address')
                                            ->email()
                                            ->autocomplete(false)
                                            ->unique('users', 'email', ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->ignore(auth()->id()))
                                            ->prefixIcon(Heroicon::OutlinedAtSymbol)
                                            ->prefixIconColor('primary')
                                            ->validationMessages([
                                                'email' => 'Invalid email address.',
                                                'required' => 'Email address is required.',
                                                'unique' => 'This email address is already in use.',
                                            ])
                                            ->required(),

                                        TextInput::make('phone')
                                            ->label('Phone Number')
                                            ->tel()
                                            ->telRegex('/^(?:\+254|254|0)(7\d{8}|1\d{8})$/')
                                            ->unique('users', 'phone', ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->ignore(auth()->id()))
                                            ->prefixIcon(Heroicon::OutlinedPhone)
                                            ->prefixIconColor('primary')
                                            ->validationMessages([
                                                'unique' => 'This phone number is already in use.',
                                                'required' => 'Phone number is required.',
                                                'regex' => 'Invalid phone number.',
                                            ])
                                            ->required(),

                                        TextInput::make('account_no')
                                            ->label('Account Number')
                                            ->prefixIcon(Heroicon::Hashtag)
                                            ->prefixIconColor('primary')
                                            ->disabled(),
                                    ])->columns(2),
                            ]),

                        Tab::make('Security')
                            ->icon(Heroicon::OutlinedLockClosed)
                            ->schema([
                                Section::make('Change Password')
                                    ->description('Leave blank to keep your current password.')
                                    ->schema([
                                        TextInput::make('current_password')
                                            ->label('Current Password')
                                            ->prefixIcon(Heroicon::OutlinedLockClosed)
                                            ->prefixIconColor('primary')
                                            ->password()
                                            ->revealable()
                                            ->requiredWith('password')
                                            ->currentPassword(),

                                        TextInput::make('password')
                                            ->label('New Password')
                                            ->prefixIcon(Heroicon::OutlinedLockClosed)
                                            ->prefixIconColor('primary')
                                            ->password()
                                            ->revealable()
                                            ->requiredWith('current_password')
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->rules([
                                                'confirmed',
                                                'regex:/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/',
                                            ])
                                            ->validationMessages([
                                                'regex' => 'Password must be at least 8 characters, contain one uppercase letter, and one special character.',
                                                'confirmed' => 'Password confirmation does not match.',
                                                'required' => 'Password is required.',
                                            ]),

                                        TextInput::make('password_confirmation')
                                            ->label('Confirm Password')
                                            ->prefixIcon(Heroicon::OutlinedLockClosed)
                                            ->prefixIconColor('primary')
                                            ->password()
                                            ->revealable()
                                            ->dehydrated(false)
                                            ->requiredWith('password'),
                                    ])->compact()->columns(1),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->contained(false)
                    ->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        try {
            $user = auth()->user();

            $this->validate([
                'data.linked_id' => 'nullable|integer',
                'data.username' => 'required|string|max:255|unique:users,username,'.$user->id,
                'data.name' => 'required|string|max:255',
                'data.email' => 'required|email|unique:users,email,'.$user->id,
                'data.phone' => ['required', 'string', 'regex:/^(?:\+254|254|0)(7\d{8}|1\d{8})$/', 'unique:users,phone,'.$user->id],
                'data.profile_photo' => 'nullable|array',
                'data.profile_photo.*' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:10240',
            ]);

            $data = $this->data;

            $user->update([
                'linked_id' => $data['linked_id'] ?? null,
                'username' => $data['username'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'account_no' => $data['account_no'],
            ]);

            if (! empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            if (isset($data['profile_photo']) && is_array($data['profile_photo'])) {
                $user->clearMediaCollection('avatars');

                foreach ($data['profile_photo'] as $tempFileData) {
                    if (isset($tempFileData['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'])) {
                        $tempPath = $tempFileData['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'];

                        if (file_exists($tempPath)) {
                            $user->addMedia($tempPath)->toMediaCollection('avatars');
                        }
                    }
                }
            }

            Notification::make()
                ->success()
                ->title('Profile updated')
                ->body('Your profile has been successfully updated.')
                ->send();

            $this->form->fill([
                ...$data,
                'current_password' => null,
                'password' => null,
                'password_confirmation' => null,
            ]);
        } catch (Halt $exception) {
            return;
        } catch (\Exception $e) {
            Log::error(
                'Error updating profile',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]
            );
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('There was an error updating your profile. Please try again.')
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
