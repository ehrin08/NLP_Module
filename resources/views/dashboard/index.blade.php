<x-layouts.app>
    <section class="space-y-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Analytics</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">Customer Satisfaction Dashboard</h1>
            </div>
            <a href="{{ route('feedback.index') }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                Manage Reviews
            </a>
        </div>

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

        <div class="grid gap-4 md:grid-cols-3">
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

        <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-zinc-950">Sentiment Distribution</h2>
                <div class="mt-4 h-80">
                    <canvas id="sentimentPie"></canvas>
                </div>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-zinc-950">14-Day Sentiment Trend</h2>
                <div class="mt-4 h-80">
                    <canvas id="trendLine"></canvas>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
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
                <h2 class="text-lg font-semibold text-zinc-950">Filipino/Taglish Sentiment Trend</h2>
                <div class="mt-4 h-80">
                    <canvas id="taglishTrendLine"></canvas>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white shadow-sm">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h2 class="text-lg font-semibold text-zinc-950">Latest Reviews</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm">
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
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const pieContext = document.getElementById('sentimentPie');
        const trendContext = document.getElementById('trendLine');
        const taglishTrendContext = document.getElementById('taglishTrendLine');

        new Chart(pieContext, {
            type: 'pie',
            data: {
                labels: @json($pieLabels),
                datasets: [{
                    data: @json($pieData),
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });

        new Chart(trendContext, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [
                    {
                        label: 'Positive',
                        data: @json($trendData['Positive']),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Neutral',
                        data: @json($trendData['Neutral']),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Negative',
                        data: @json($trendData['Negative']),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.12)',
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });

        new Chart(taglishTrendContext, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [
                    {
                        label: 'Positive Taglish',
                        data: @json($taglishTrendData['Positive']),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Neutral Taglish',
                        data: @json($taglishTrendData['Neutral']),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.12)',
                        tension: 0.35,
                    },
                    {
                        label: 'Negative Taglish',
                        data: @json($taglishTrendData['Negative']),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.12)',
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                    },
                },
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    </script>
</x-layouts.app>
