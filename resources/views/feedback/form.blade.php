@php
    $feedback = $feedback ?? null;
    $buttonText = $buttonText ?? 'Save';
    $showCancel = $showCancel ?? true;
@endphp

@csrf

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="customer_name" class="block text-sm font-medium text-zinc-800">Customer name</label>
        <input
            id="customer_name"
            name="customer_name"
            type="text"
            value="{{ old('customer_name', $feedback->customer_name ?? '') }}"
            required
            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none ring-teal-600 focus:ring-2"
        >
        <p class="mt-2 text-sm text-red-600" data-field-error="customer_name">@error('customer_name'){{ $message }}@enderror</p>
    </div>

    <div>
        <label for="service_name" class="block text-sm font-medium text-zinc-800">Service name</label>
        <input
            id="service_name"
            name="service_name"
            type="text"
            value="{{ old('service_name', $feedback->service_name ?? '') }}"
            required
            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none ring-teal-600 focus:ring-2"
        >
        <p class="mt-2 text-sm text-red-600" data-field-error="service_name">@error('service_name'){{ $message }}@enderror</p>
    </div>

    <div>
        <label for="rating" class="block text-sm font-medium text-zinc-800">Rating</label>
        <select
            id="rating"
            name="rating"
            required
            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none ring-teal-600 focus:ring-2"
        >
            @for ($score = 5; $score >= 1; $score--)
                <option value="{{ $score }}" @selected((int) old('rating', $feedback->rating ?? 5) === $score)>
                    {{ $score }}
                </option>
            @endfor
        </select>
        <p class="mt-2 text-sm text-red-600" data-field-error="rating">@error('rating'){{ $message }}@enderror</p>
    </div>

    <div class="md:col-span-2">
        <label for="feedback_text" class="block text-sm font-medium text-zinc-800">Feedback</label>
        <textarea
            id="feedback_text"
            name="feedback_text"
            rows="7"
            required
            class="mt-2 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm outline-none ring-teal-600 focus:ring-2"
        >{{ old('feedback_text', $feedback->feedback_text ?? '') }}</textarea>
        <p class="mt-2 text-sm text-red-600" data-field-error="feedback_text">@error('feedback_text'){{ $message }}@enderror</p>
    </div>
</div>

<div class="mt-6 flex flex-wrap items-center gap-3">
    <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-70" data-submit-label>
        {{ $buttonText }}
    </button>

    @if ($showCancel && auth()->check())
        <a href="{{ route('feedback.index') }}" class="rounded-lg border border-zinc-300 px-5 py-2.5 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
            Cancel
        </a>
    @endif
</div>
