<x-layouts.app>
    <section class="space-y-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Analytics</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">Customer Satisfaction Dashboard</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" data-modal-open="dashboard-filter-modal" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                    Filters
                </button>
                <button type="button" data-modal-open="dashboard-settings-modal" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                    Dashboard Settings
                </button>
                <a href="{{ route('feedback.index') }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                    Manage Reviews
                </a>
            </div>
        </div>

        <div data-async-section="dashboard">
            @include('dashboard.partials.content')
        </div>
    </section>

    <x-modal id="dashboard-filter-modal" title="Filter Dashboard" maxWidth="4xl">
        <x-feedback-filters :action="route('dashboard')" :filters="$filters" :options="$filterOptions" />
    </x-modal>

    <x-modal id="dashboard-settings-modal" title="Dashboard Settings" maxWidth="xl">
        <form data-dashboard-settings-form action="{{ route('dashboard') }}" class="space-y-5">
            <div>
                <label for="latest_limit" class="block text-sm font-medium text-zinc-800">Latest review rows</label>
                <input
                    id="latest_limit"
                    name="latest_limit"
                    type="number"
                    min="4"
                    max="20"
                    value="{{ $latestLimit }}"
                    class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none ring-teal-600 focus:ring-2"
                >
            </div>

            <label class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 px-4 py-3">
                <span>
                    <span class="block text-sm font-medium text-zinc-900">Animate charts</span>
                    <span class="block text-xs text-zinc-500">Keeps chart updates smooth during async refreshes.</span>
                </span>
                <input type="checkbox" name="animate_charts" value="1" class="h-4 w-4 rounded border-zinc-300 text-teal-700 focus:ring-teal-600">
            </label>

            <label class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 px-4 py-3">
                <span>
                    <span class="block text-sm font-medium text-zinc-900">Compact latest review table</span>
                    <span class="block text-xs text-zinc-500">Reduces row height for denser review scanning.</span>
                </span>
                <input type="checkbox" name="compact_table" value="1" class="h-4 w-4 rounded border-zinc-300 text-teal-700 focus:ring-teal-600">
            </label>

            <div class="flex justify-end gap-3">
                <button type="button" data-modal-close class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                    Save Settings
                </button>
            </div>
        </form>
    </x-modal>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</x-layouts.app>
