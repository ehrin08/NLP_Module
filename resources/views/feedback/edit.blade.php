<x-layouts.app>
    <section class="mx-auto max-w-3xl">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Admin</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950">Edit Feedback</h1>
        </div>

        <form method="POST" action="{{ route('feedback.update', $feedback) }}" class="rounded-lg border border-zinc-200 bg-white p-6 shadow-sm">
            @method('PUT')
            @include('feedback.form', ['buttonText' => 'Save changes'])
        </form>
    </section>
</x-layouts.app>
