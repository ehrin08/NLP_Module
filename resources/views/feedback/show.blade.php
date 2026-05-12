<x-layouts.app>
    <section class="mx-auto max-w-3xl">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Review</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">{{ $feedback->customer_name }}</h1>
            </div>
            <a href="{{ route('feedback.index') }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                Back
            </a>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-zinc-500">Service</p>
                    <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->service_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-zinc-500">Rating</p>
                    <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->rating }}/5</p>
                </div>
                <div>
                    <p class="text-sm text-zinc-500">Sentiment</p>
                    <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->predicted_sentiment }}</p>
                </div>
                <div>
                    <p class="text-sm text-zinc-500">Confidence</p>
                    <p class="mt-1 font-semibold text-zinc-950">{{ number_format($feedback->confidence_score * 100, 1) }}%</p>
                </div>
            </div>

            <div class="mt-6 rounded-lg bg-zinc-50 p-4 text-sm leading-6 text-zinc-700">
                {{ $feedback->feedback_text }}
            </div>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('feedback.edit', $feedback) }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                    Edit
                </a>
                <form method="POST" action="{{ route('feedback.destroy', $feedback) }}" onsubmit="return confirm('Delete this feedback?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
