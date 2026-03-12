<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

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
                Wizard::make([
                    Wizard\Step::make('Account Information')
                        ->icon(Heroicon::OutlinedUser)
                        ->completedIcon(Heroicon::OutlinedCheckCircle)
                        ->description('Your basic account details')
                        ->schema([
                            Section::make('Profile Photo')->schema([
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
                                    ->maxSize(10240) // 10MB max
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif']),
                            ])->columns(1)->compact(),
                            Section::make('Account Details')->schema([
                                TextInput::make('username')
                                    ->label('Username')
                                    ->prefixIcon(Heroicon::OutlinedUserCircle)
                                    ->prefixIconColor('primary')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('users', 'username', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                        return $rule->ignore(auth()->id());
                                    }),

                                TextInput::make('name')
                                    ->label('Full name')
                                    ->prefixIcon(Heroicon::OutlinedUser)
                                    ->prefixIconColor('primary')
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->autocomplete(false)
                                    ->unique('users', 'email', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                        return $rule->ignore(auth()->id());
                                    })
                                    ->prefixIcon(Heroicon::OutlinedAtSymbol)
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
                                    ->unique('users', 'phone', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                        return $rule->ignore(auth()->id());
                                    })
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
                    Wizard\Step::make('Account Security')
                        ->icon(Heroicon::OutlinedLockClosed)
                        ->completedIcon(Heroicon::OutlinedCheckCircle)
                        ->description('Update your password')
                        ->schema([
                            Section::make('Change Password')
                                ->description('Leave blank to keep your current password')
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
                                        ->label('Password')
                                        ->prefixIcon(Heroicon::OutlinedLockClosed)
                                        ->prefixIconColor('primary')
                                        ->password()
                                        ->revealable()
                                        ->requiredWith('current_password')
                                        ->dehydrated(fn ($state) => filled($state)) // only send if filled
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
                                        ->requiredWith('password'),
                                ])
                                ->columns(1)->compact(),
                        ]),
                ])
                    ->columnSpanFull()
                    ->contained(false)
                    ->submitAction($this->getSubmitAction()),
            ])
            ->statePath('data');
    }

    protected function getSubmitAction(): View
    {
        return view('filament.components.submit-button');
    }

    public function save(): void
    {
        try {
            $data = $this->data;
            $user = auth()->user();

            // Debug logging
            \Log::info('EditProfilePage save called', [
                'user_id' => $user->id,
                'data_keys' => array_keys($data),
                'photo_data' => isset($data['profile_photo']) ? gettype($data['profile_photo']) : 'not_set',
                'photo_value' => $data['profile_photo'] ?? null,
            ]);

            // Validate the data first
            $phoneRegex = '/^(?:\+254|254|0)(7\d{8}|1\d{8})$/';
            $this->validate([
                'data.username' => 'required|string|max:255|unique:users,username,'.$user->id,
                'data.name' => 'required|string|max:255',
                'data.email' => 'required|email|unique:users,email,'.$user->id,
                'data.phone' => ['required', 'string', 'regex:'.$phoneRegex, 'unique:users,phone,'.$user->id],
                'data.profile_photo' => 'nullable|array', // SpatieMediaLibraryFileUpload sends array
                'data.profile_photo.*' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:10240', // Validate each file
            ]);

            // Use validated data
            $validated = $this->data;

            // Update user fields
            $user->update([
                'username' => $validated['username'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'account_no' => $data['account_no'],
            ]);

            // Update password if provided
            if (! empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password']),
                ]);
            }

            // Handle profile photo upload manually
            if (isset($data['profile_photo']) && is_array($data['profile_photo'])) {
                // Clear existing media first
                $user->clearMediaCollection('avatars');

                // Process the uploaded file
                foreach ($data['profile_photo'] as $tempFileData) {
                    if (isset($tempFileData['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'])) {
                        $tempPath = $tempFileData['Livewire\\Features\\SupportFileUploads\\TemporaryUploadedFile'];
                        if (file_exists($tempPath)) {
                            $user->addMedia($tempPath)
                                ->toMediaCollection('avatars');
                        }
                    }
                }
            }

            Notification::make()
                ->success()
                ->title('Profile updated')
                ->body('Your profile has been successfully updated.')
                ->send();

            // Reset password fields after successful save
            $this->form->fill([
                ...$data,
                'current_password' => null,
                'password' => null,
                'password_confirmation' => null,
            ]);
        } catch (Halt $exception) {
            return;
        } catch (\Exception $e) {
            \Log::error('EditProfilePage save error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

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
