<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Name -->
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input
                    name="name"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Full name')"
                    :invalid="$errors->has('name')"
                />
                <flux:error name="name" />
            </flux:field>

            <!-- Email Address -->
            <flux:field>
                <flux:label>{{ __('Email address') }}</flux:label>
                <flux:input
                    name="email"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                    :invalid="$errors->has('email')"
                />
                <flux:error name="email" />
            </flux:field>

            <!-- Phone Number -->
            <flux:field>
                <flux:label>{{ __('Phone Number') }}</flux:label>
                <flux:input
                    name="phone"
                    :value="old('phone')"
                    type="text"
                    required
                    placeholder="254712345678 or 0712345678"
                    :invalid="$errors->has('phone')"
                />
                <flux:error name="phone" />
            </flux:field>

            <!-- Password -->
            <flux:field>
                <flux:label>{{ __('Password') }}</flux:label>
                <flux:input
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    viewable
                    :invalid="$errors->has('password')"
                />
                @if (app()->isProduction())
                    <flux:description>Min. 12 characters with uppercase, lowercase, numbers and symbols.</flux:description>
                @else
                    <flux:description>Min. 8 characters.</flux:description>
                @endif
                <flux:error name="password" />
            </flux:field>

            <!-- Confirm Password -->
            <flux:field>
                <flux:label>{{ __('Confirm password') }}</flux:label>
                <flux:input
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    viewable
                    :invalid="$errors->has('password_confirmation')"
                />
                <flux:error name="password_confirmation" />
            </flux:field>

            <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
