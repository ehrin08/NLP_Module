@props([
    'action',
    'filters',
    'options',
    'showSort' => true,
])

<form method="GET" action="{{ $action }}" class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm" data-filter-form data-async-url="{{ $action }}">
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Search</span>
            <input name="search" value="{{ $filters['search'] }}" type="search" placeholder="Customer, feedback, service"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Sentiment</span>
            <select name="sentiment" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                <option value="">All sentiments</option>
                @foreach ($options['sentiments'] as $sentiment)
                    <option value="{{ $sentiment }}" @selected($filters['sentiment'] === $sentiment)>{{ $sentiment }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Service</span>
            <select name="service" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                <option value="">All services</option>
                @foreach ($options['services'] as $service)
                    <option value="{{ $service }}" @selected($filters['service'] === $service)>{{ $service }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Date Range</span>
            <select name="date_preset" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                <option value="">All dates</option>
                @foreach ($options['datePresets'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['date_preset'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">From</span>
            <input name="start_date" value="{{ $filters['start_date'] }}" type="date"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">To</span>
            <input name="end_date" value="{{ $filters['end_date'] }}" type="date"
                class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Rating</span>
            <select name="rating" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                <option value="">All ratings</option>
                @for ($rating = 5; $rating >= 1; $rating--)
                    <option value="{{ $rating }}" @selected($filters['rating'] === (string) $rating)>{{ $rating }} {{ Illuminate\Support\Str::plural('Star', $rating) }}</option>
                @endfor
            </select>
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Confidence</span>
            <select name="confidence_min" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                <option value="">All confidence scores</option>
                <option value="0.70" @selected($filters['confidence_min'] === '0.70')>Confidence &gt; 70%</option>
                <option value="0.80" @selected($filters['confidence_min'] === '0.80')>Confidence &gt; 80%</option>
                <option value="0.90" @selected($filters['confidence_min'] === '0.90')>Confidence &gt; 90%</option>
            </select>
        </label>

        <label class="space-y-1.5">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Language</span>
            <select name="language" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                <option value="">All languages</option>
                @foreach ($options['languages'] as $value => $label)
                    <option value="{{ $value }}" @selected($filters['language'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        @if ($showSort)
            <label class="space-y-1.5">
                <span class="text-xs font-semibold uppercase tracking-wide text-zinc-600">Sort</span>
                <select name="sort" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100">
                    @foreach ($options['sorts'] as $value => $label)
                        <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        @endif
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            Apply Filters
        </button>
        <a href="{{ $action }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
            Reset
        </a>
    </div>
</form>

@once
    <script>
        document.querySelectorAll('[data-filter-form]').forEach((form) => {
            const datePreset = form.querySelector('[name="date_preset"]');
            const dateInputs = form.querySelectorAll('[name="start_date"], [name="end_date"]');

            dateInputs.forEach((input) => {
                input.addEventListener('change', () => {
                    if (input.value && datePreset) {
                        datePreset.value = 'custom';
                    }
                });
            });
        });
    </script>
@endonce
