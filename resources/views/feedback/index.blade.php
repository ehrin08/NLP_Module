<x-layouts.app>
    <section>
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Admin</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">Feedback Reviews</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" data-modal-open="feedback-filter-modal" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                    Filters
                </button>
                <button
                    type="button"
                    data-modal-open="feedback-form-modal"
                    data-feedback-create
                    data-store-url="{{ route('feedback.store') }}"
                    class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                >
                    Add Feedback
                </button>
                <a href="{{ route('dashboard') }}" class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                    Dashboard
                </a>
            </div>
        </div>

        <div data-async-section="feedback">
            @include('feedback.partials.table', ['feedback' => $feedback])
        </div>
    </section>

    <x-modal id="feedback-filter-modal" title="Filter Feedback" maxWidth="4xl">
        <x-feedback-filters :action="route('feedback.index')" :filters="$filters" :options="$filterOptions" />
    </x-modal>

    <x-modal id="feedback-form-modal" title="Add Feedback" maxWidth="3xl">
        <form method="POST" action="{{ route('feedback.store') }}" data-feedback-form data-store-url="{{ route('feedback.store') }}">
            <div data-feedback-form-method></div>
            @include('feedback.form', ['buttonText' => 'Save feedback', 'showCancel' => false])
        </form>
    </x-modal>

    <x-modal id="feedback-show-modal" title="Feedback Details" maxWidth="2xl">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-sm text-zinc-500">Customer</p>
                <p class="mt-1 font-semibold text-zinc-950" data-feedback-show="customer_name"></p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Service</p>
                <p class="mt-1 font-semibold text-zinc-950" data-feedback-show="service_name"></p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Rating</p>
                <p class="mt-1 font-semibold text-zinc-950" data-feedback-show="rating"></p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Confidence</p>
                <p class="mt-1 font-semibold text-zinc-950" data-feedback-show="confidence_score"></p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Sentiment</p>
                <p class="mt-1 font-semibold text-zinc-950" data-feedback-show="predicted_sentiment"></p>
            </div>
            <div>
                <p class="text-sm text-zinc-500">Submitted</p>
                <p class="mt-1 font-semibold text-zinc-950" data-feedback-show="created_at"></p>
            </div>
        </div>

        <div class="mt-6 rounded-lg bg-zinc-50 p-4 text-sm leading-6 text-zinc-700" data-feedback-show="feedback_text"></div>
    </x-modal>

    <x-modal id="feedback-delete-modal" title="Delete Feedback" maxWidth="lg">
        <div class="space-y-4">
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                This action will permanently remove the selected feedback entry.
            </div>

            <p class="text-sm text-zinc-600">
                Delete feedback from <span class="font-semibold text-zinc-950" data-feedback-delete-name></span>?
            </p>

            <form method="POST" data-feedback-delete-form>
                @csrf
                @method('DELETE')
                <div class="flex justify-end gap-3">
                    <button type="button" data-modal-close class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-semibold text-zinc-700 hover:bg-zinc-100">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</x-layouts.app>
