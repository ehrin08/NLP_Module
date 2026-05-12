<x-layouts.app>
    <section>
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Admin</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">Feedback Reviews</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                Dashboard
            </a>
        </div>

        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
                    <thead class="bg-zinc-100 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                        <tr>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Rating</th>
                            <th class="px-4 py-3">Sentiment</th>
                            <th class="px-4 py-3">Confidence</th>
                            <th class="px-4 py-3">Submitted</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($feedback as $item)
                            <tr class="align-top">
                                <td class="px-4 py-3 font-medium text-zinc-950">{{ $item->customer_name }}</td>
                                <td class="px-4 py-3 text-zinc-700">{{ $item->service_name }}</td>
                                <td class="px-4 py-3 text-zinc-700">{{ $item->rating }}/5</td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'rounded-lg px-2.5 py-1 text-xs font-semibold',
                                        'bg-emerald-100 text-emerald-800' => $item->predicted_sentiment === 'Positive',
                                        'bg-amber-100 text-amber-800' => $item->predicted_sentiment === 'Neutral',
                                        'bg-red-100 text-red-800' => $item->predicted_sentiment === 'Negative',
                                    ])>
                                        {{ $item->predicted_sentiment }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-zinc-700">{{ number_format($item->confidence_score * 100, 1) }}%</td>
                                <td class="px-4 py-3 text-zinc-700">{{ $item->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('feedback.show', $item) }}" class="rounded-lg border border-zinc-300 px-3 py-1.5 font-semibold text-zinc-700 hover:bg-zinc-100">
                                            View
                                        </a>
                                        <a href="{{ route('feedback.edit', $item) }}" class="rounded-lg border border-zinc-300 px-3 py-1.5 font-semibold text-zinc-700 hover:bg-zinc-100">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('feedback.destroy', $item) }}" onsubmit="return confirm('Delete this feedback?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-600 px-3 py-1.5 font-semibold text-white hover:bg-red-700">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-zinc-500">No feedback has been submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $feedback->links() }}
        </div>
    </section>
</x-layouts.app>
