@props([
    'id',
    'title' => null,
    'maxWidth' => '2xl',
])

@php
    $widthClasses = [
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
    ];
@endphp

<div
    id="{{ $id }}"
    data-modal
    class="fixed inset-0 z-50 hidden items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
>
    <div data-modal-backdrop class="absolute inset-0 bg-zinc-950/45 backdrop-blur-sm"></div>

    <div
        data-modal-panel
        class="relative z-10 flex max-h-[90vh] w-full {{ $widthClasses[$maxWidth] ?? $widthClasses['2xl'] }} flex-col overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-2xl transition duration-200 ease-out"
    >
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4">
            <div>
                @if ($title)
                    <h2 class="text-lg font-semibold text-zinc-950" data-modal-title>{{ $title }}</h2>
                @else
                    <div data-modal-title-container>{{ $header ?? '' }}</div>
                @endif
            </div>

            <button
                type="button"
                data-modal-close
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800"
                aria-label="Close modal"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M4.22 4.22a.75.75 0 0 1 1.06 0L10 8.94l4.72-4.72a.75.75 0 1 1 1.06 1.06L11.06 10l4.72 4.72a.75.75 0 1 1-1.06 1.06L10 11.06l-4.72 4.72a.75.75 0 0 1-1.06-1.06L8.94 10 4.22 5.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto px-5 py-5">
            {{ $slot }}
        </div>
    </div>
</div>
