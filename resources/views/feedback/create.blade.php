<x-layouts.app>
    <section class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-start">
        <div class="space-y-5">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Customer Feedback</p>
            <h1 class="max-w-xl text-4xl font-semibold tracking-tight text-zinc-950">Share your service experience</h1>
            <p class="max-w-xl text-base leading-7 text-zinc-600">
                Your feedback is analyzed for sentiment and helps the team monitor customer satisfaction trends.
            </p>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-2xl font-semibold text-emerald-800">Positive</p>
                    <p class="mt-1 text-sm text-emerald-700">Satisfied customers</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-2xl font-semibold text-amber-800">Neutral</p>
                    <p class="mt-1 text-sm text-amber-700">Mixed responses</p>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <p class="text-2xl font-semibold text-red-800">Negative</p>
                    <p class="mt-1 text-sm text-red-700">Service risks</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('feedback.store') }}" class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            @include('feedback.form', ['buttonText' => 'Submit feedback'])
        </form>
    </section>
</x-layouts.app>
