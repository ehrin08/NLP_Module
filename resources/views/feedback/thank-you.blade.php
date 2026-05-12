<x-layouts.app>
    <section class="mx-auto max-w-2xl rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Submitted</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">Thank you, {{ $feedback->customer_name }}</h1>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 p-4">
                <p class="text-sm text-zinc-500">Sentiment</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950">{{ $feedback->predicted_sentiment }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 p-4">
                <p class="text-sm text-zinc-500">Confidence</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950">{{ number_format($feedback->confidence_score * 100, 1) }}%</p>
            </div>
            <div class="rounded-lg border border-zinc-200 p-4">
                <p class="text-sm text-zinc-500">Rating</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950">{{ $feedback->rating }}/5</p>
            </div>
        </div>

        <div class="mt-6 rounded-lg bg-zinc-50 p-4 text-sm leading-6 text-zinc-700">
            {{ $feedback->feedback_text }}
        </div>

        <a href="{{ route('feedback.create') }}" class="mt-6 inline-flex rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
            Submit another response
        </a>
    </section>
</x-layouts.app>
