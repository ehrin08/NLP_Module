<div
    data-dashboard-state
    data-chart-labels='@json($trendLabels)'
    data-pie-labels='@json($pieLabels)'
    data-pie-data='@json($pieData)'
    data-trend-data='@json($trendData)'
    data-taglish-trend-data='@json($taglishTrendData)'
    data-language-trend-title="{{ $languageTrendTitle }}"
>
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-emerald-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">Total Positive Reviews</p>
            <p class="mt-3 text-4xl font-semibold text-emerald-800">{{ $summary['Positive'] }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-amber-700">Total Neutral Reviews</p>
            <p class="mt-3 text-4xl font-semibold text-amber-800">{{ $summary['Neutral'] }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-red-700">Total Negative Reviews</p>
            <p class="mt-3 text-4xl font-semibold text-red-800">{{ $summary['Negative'] }}</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-zinc-600">Positive vs Negative Ratio</p>
            <p class="mt-3 text-4xl font-semibold text-zinc-950">{{ $positiveNegativeRatio }}</p>
            <p class="mt-2 text-sm text-zinc-500">{{ $positiveShare }}% positive share excluding neutral reviews</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm md:col-span-2">
            <h2 class="text-lg font-semibold text-zinc-950">Frequent Complaints</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($frequentComplaints as $complaint)
                    <div class="rounded-lg bg-zinc-50 px-4 py-3">
                        <p class="text-sm font-medium text-zinc-700">{{ $complaint['category'] }}</p>
                        <p class="mt-1 text-2xl font-semibold text-zinc-950">{{ $complaint['count'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-950">Sentiment Distribution</h2>
            <div class="mt-4 h-80">
                <canvas id="sentimentPie"></canvas>
            </div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-950">Sentiment Trend</h2>
            <div class="mt-4 h-80">
                <canvas id="trendLine"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-950">Most Common Negative Taglish Words</h2>
            <div class="mt-4 space-y-3">
                @forelse ($negativeTaglishWords as $item)
                    <div class="flex items-center justify-between gap-4 rounded-lg bg-zinc-50 px-4 py-3">
                        <span class="text-sm font-medium text-zinc-800">{{ $item['word'] }}</span>
                        <span class="rounded-lg bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-800">{{ $item['count'] }}</span>
                    </div>
                @empty
                    <p class="rounded-lg bg-zinc-50 px-4 py-6 text-sm text-zinc-500">No negative Taglish terms yet.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-zinc-950" data-language-trend-title>{{ $languageTrendTitle }}</h2>
            <div class="mt-4 h-80">
                <canvas id="taglishTrendLine"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-zinc-200 bg-white shadow-sm">
        <div class="border-b border-zinc-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-zinc-950">Latest Reviews</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm" data-dashboard-latest-table>
                <thead class="bg-zinc-100 text-left text-xs font-semibold uppercase tracking-wide text-zinc-600">
                    <tr>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Sentiment</th>
                        <th class="px-4 py-3">Confidence</th>
                        <th class="px-4 py-3">Feedback</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse ($latestReviews as $review)
                        <tr>
                            <td class="px-4 py-3 font-medium text-zinc-950">{{ $review->customer_name }}</td>
                            <td class="px-4 py-3 text-zinc-700">{{ $review->service_name }}</td>
                            <td class="px-4 py-3 text-zinc-700">{{ $review->predicted_sentiment }}</td>
                            <td class="px-4 py-3 text-zinc-700">{{ number_format($review->confidence_score * 100, 1) }}%</td>
                            <td class="max-w-md px-4 py-3 text-zinc-700">{{ Illuminate\Support\Str::limit($review->feedback_text, 100) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-zinc-500">No feedback has been submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
