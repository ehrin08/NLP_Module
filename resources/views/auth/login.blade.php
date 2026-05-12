<x-layouts.app>
    <section class="mx-auto max-w-md">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Admin</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">Sign in</h1>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            @csrf

            <div class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-800">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none ring-teal-600 focus:ring-2"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-800">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none ring-teal-600 focus:ring-2"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-zinc-700">
                    <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300 text-teal-700 focus:ring-teal-600">
                    Remember me
                </label>

                <button type="submit" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
                    Sign in
                </button>
            </div>
        </form>
    </section>
</x-layouts.app>
