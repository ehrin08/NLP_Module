<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sentiment Analysis Module') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">
    <x-toast-stack />

    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('feedback.create') }}" class="text-lg font-semibold tracking-tight text-zinc-950">
                Sentiment Analysis Module
            </a>

            <nav class="flex flex-wrap items-center gap-2 text-sm font-medium">
                <a href="{{ route('feedback.create') }}" class="rounded-lg px-3 py-2 text-zinc-700 hover:bg-zinc-100">
                    Submit Feedback
                </a>

                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-zinc-700 hover:bg-zinc-100">
                        Dashboard
                    </a>
                    <a href="{{ route('feedback.index') }}" class="rounded-lg px-3 py-2 text-zinc-700 hover:bg-zinc-100">
                        Reviews
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-700">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg bg-zinc-900 px-3 py-2 text-white hover:bg-zinc-700">
                        Admin Login
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>
