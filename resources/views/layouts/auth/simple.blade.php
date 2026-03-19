<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-white antialiased font-sans">

        {{-- Top accent line --}}
        <div class="h-px w-full bg-linear-to-r from-transparent via-zinc-700 to-transparent"></div>

        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-12">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="mb-8 flex flex-col items-center gap-3" wire:navigate>
                <div class="flex size-12 items-center justify-center rounded-xl bg-zinc-900 ring-1 ring-zinc-800">
                    <x-app-logo-icon class="size-7 fill-current text-white" />
                </div>
                <span class="text-sm font-medium text-zinc-400">{{ config('app.name') }}</span>
            </a>

            {{-- Card --}}
            <div class="w-full max-w-sm rounded-xl bg-zinc-900 p-8 ring-1 ring-zinc-800">
                {{ $slot }}
            </div>

        </div>

        @fluxScripts
    </body>
</html>
