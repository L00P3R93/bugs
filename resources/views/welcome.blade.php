<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-950 text-white antialiased font-sans">

        {{-- Top accent line --}}
        <div class="h-px w-full bg-linear-to-r from-transparent via-zinc-700 to-transparent"></div>

        <div class="flex min-h-screen flex-col">

            {{-- Navigation --}}
            @if (Route::has('login'))
                <header class="flex items-center justify-end px-8 py-5">
                    <nav class="flex items-center gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded px-4 py-1.5 text-sm font-medium text-zinc-300 ring-1 ring-zinc-700 transition hover:bg-zinc-800 hover:text-white">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded px-4 py-1.5 text-sm font-medium text-zinc-400 transition hover:text-white">
                                Log in
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="rounded px-4 py-1.5 text-sm font-medium text-zinc-900 bg-white transition hover:bg-zinc-100">
                                    Get started
                                </a>
                            @endif
                        @endauth
                    </nav>
                </header>
            @endif

            {{-- Hero --}}
            <main class="flex flex-1 flex-col items-center justify-center px-6 py-20 text-center">

                {{-- Logo --}}
                <div class="mb-8 flex size-14 items-center justify-center rounded-xl bg-zinc-900 ring-1 ring-zinc-800">
                    <x-app-logo-icon class="size-8 fill-current text-white" />
                </div>

                {{-- Headline --}}
                <h1 class="mb-3 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                    {{ config('app.name') }}
                </h1>

                {{-- Tagline --}}
                <p class="mb-10 max-w-sm text-base text-zinc-400 leading-relaxed">
                    Find vulnerabilities. Report responsibly.<br>Get rewarded.
                </p>

                {{-- Divider --}}
                <div class="mb-10 h-px w-16 bg-zinc-800"></div>

                {{-- CTAs --}}
                @guest
                    <div class="flex flex-col items-center gap-3 sm:flex-row">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md bg-white px-6 py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition hover:bg-zinc-100">
                                Create an account
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md px-6 py-2.5 text-sm font-medium text-zinc-400 ring-1 ring-zinc-800 transition hover:bg-zinc-900 hover:text-white">
                            Log in
                        </a>
                    </div>
                @else
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-md bg-white px-6 py-2.5 text-sm font-medium text-zinc-900 shadow-sm transition hover:bg-zinc-100">
                        Go to Dashboard
                    </a>
                @endguest

            </main>

            {{-- Footer --}}
            <footer class="pb-8 text-center text-xs text-zinc-600">
                Secure the web &mdash; one bug at a time.
            </footer>

        </div>

    </body>
</html>
