<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <p class="text-sm text-zinc-500">Customer</p>
        <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->customer_name }}</p>
    </div>
    <div>
        <p class="text-sm text-zinc-500">Service</p>
        <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->service_name }}</p>
    </div>
    <div>
        <p class="text-sm text-zinc-500">Rating</p>
        <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->rating }}/5</p>
    </div>
    <div>
        <p class="text-sm text-zinc-500">Confidence</p>
        <p class="mt-1 font-semibold text-zinc-950">{{ number_format($feedback->confidence_score * 100, 1) }}%</p>
    </div>
    <div>
        <p class="text-sm text-zinc-500">Sentiment</p>
        <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->predicted_sentiment }}</p>
    </div>
    <div>
        <p class="text-sm text-zinc-500">Submitted</p>
        <p class="mt-1 font-semibold text-zinc-950">{{ $feedback->created_at->format('M d, Y h:i A') }}</p>
    </div>
</div>

<div class="mt-6 rounded-lg bg-zinc-50 p-4 text-sm leading-6 text-zinc-700">
    {{ $feedback->feedback_text }}
</div>
