<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FeedbackFilterService
{
    public const SENTIMENTS = ['Positive', 'Neutral', 'Negative'];

    public const DATE_PRESETS = [
        'today' => 'Today',
        'last_7_days' => 'Last 7 Days',
        'last_30_days' => 'Last 30 Days',
        'this_month' => 'This Month',
        'custom' => 'Custom Date Range',
    ];

    public const SORTS = [
        'newest' => 'Newest',
        'oldest' => 'Oldest',
        'highest_rating' => 'Highest Rating',
        'lowest_rating' => 'Lowest Rating',
        'highest_confidence' => 'Highest Confidence',
        'lowest_confidence' => 'Lowest Confidence',
    ];

    public const LANGUAGES = [
        'taglish' => 'Taglish Reviews Only',
        'english' => 'English Reviews Only',
        'filipino' => 'Filipino Reviews Only',
    ];

    private const FILIPINO_TERMS = [
        'ako', 'kami', 'ikaw', 'sila', 'yung', 'ang', 'mga', 'ng', 'sa', 'na', 'pa', 'po', 'opo',
        'naman', 'talaga', 'hindi', 'di', 'wala', 'walang', 'sulit', 'mabait', 'matagal',
        'mainit', 'malamig', 'maingay', 'presyo', 'pwede', 'puwede', 'sakto', 'lang',
        'masakit', 'bitin', 'naasikaso', 'magaling', 'malinis', 'salamat', 'ayos',
    ];

    private const ENGLISH_TERMS = [
        'the', 'and', 'very', 'good', 'bad', 'great', 'poor', 'staff', 'service', 'massage',
        'facial', 'spa', 'clean', 'friendly', 'wait', 'price', 'room', 'experience',
        'therapist', 'relaxing', 'excellent', 'rushed', 'comfortable',
    ];

    public function fromRequest(Request $request): array
    {
        $filters = [
            'sentiment' => $this->allowedValue($request->query('sentiment'), self::SENTIMENTS),
            'date_preset' => $this->allowedValue($request->query('date_preset'), array_keys(self::DATE_PRESETS)),
            'start_date' => $this->dateValue($request->query('start_date')),
            'end_date' => $this->dateValue($request->query('end_date')),
            'service' => trim((string) $request->query('service')),
            'rating' => $this->allowedValue($request->query('rating'), ['1', '2', '3', '4', '5']),
            'confidence_min' => $this->allowedValue($request->query('confidence_min'), ['0.70', '0.80', '0.90']),
            'search' => trim((string) $request->query('search')),
            'language' => $this->allowedValue($request->query('language'), array_keys(self::LANGUAGES)),
            'sort' => $this->allowedValue($request->query('sort'), array_keys(self::SORTS)) ?: 'newest',
        ];

        if ($filters['date_preset'] !== 'custom') {
            $filters['start_date'] = null;
            $filters['end_date'] = null;
        }

        return $filters;
    }

    public function apply(Builder $query, array $filters, bool $withSorting = true): Builder
    {
        $query
            ->when($filters['sentiment'], fn (Builder $query, string $sentiment) => $query->where('predicted_sentiment', $sentiment))
            ->when($filters['service'], fn (Builder $query, string $service) => $query->where('service_name', $service))
            ->when($filters['rating'], fn (Builder $query, string $rating) => $query->where('rating', (int) $rating))
            ->when($filters['confidence_min'], fn (Builder $query, string $minimum) => $query->where('confidence_score', '>', (float) $minimum))
            ->when($filters['search'], fn (Builder $query, string $search) => $this->applySearch($query, $search));

        $this->applyDateRange($query, $filters);

        if ($filters['language']) {
            $this->applyLanguage($query, $filters['language']);
        }

        if ($withSorting) {
            $this->applySorting($query, $filters['sort']);
        }

        return $query;
    }

    public function applyLanguage(Builder $query, string $language): Builder
    {
        return match ($language) {
            'taglish' => $query
                ->where(fn (Builder $query) => $this->whereAnyTerm($query, self::FILIPINO_TERMS))
                ->where(fn (Builder $query) => $this->whereAnyTerm($query, self::ENGLISH_TERMS)),
            'filipino' => $query
                ->where(fn (Builder $query) => $this->whereAnyTerm($query, self::FILIPINO_TERMS))
                ->whereNot(fn (Builder $query) => $this->whereAnyTerm($query, self::ENGLISH_TERMS)),
            'english' => $query->whereNot(fn (Builder $query) => $this->whereAnyTerm($query, self::FILIPINO_TERMS)),
            default => $query,
        };
    }

    public function trendRange(array $filters): array
    {
        if ($filters['date_preset']) {
            return $this->resolveDateRange($filters) ?? [now()->subDays(13)->startOfDay(), now()->endOfDay()];
        }

        return [now()->subDays(13)->startOfDay(), now()->endOfDay()];
    }

    private function applyDateRange(Builder $query, array $filters): void
    {
        $range = $this->resolveDateRange($filters);

        if ($range) {
            $query->whereBetween('created_at', $range);
        }
    }

    private function resolveDateRange(array $filters): ?array
    {
        return match ($filters['date_preset']) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'last_7_days' => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'last_30_days' => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'custom' => $this->customRange($filters),
            default => null,
        };
    }

    private function customRange(array $filters): ?array
    {
        if (! $filters['start_date'] && ! $filters['end_date']) {
            return null;
        }

        $start = $filters['start_date']
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : Carbon::create(1970, 1, 1)->startOfDay();
        $end = $filters['end_date']
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : now()->endOfDay();

        return $start->lte($end) ? [$start, $end] : [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
    }

    private function applySearch(Builder $query, string $search): Builder
    {
        $term = '%'.str_replace(['%', '_'], ['\%', '\_'], mb_strtolower($search)).'%';

        return $query->where(function (Builder $query) use ($term) {
            $query
                ->whereRaw('LOWER(customer_name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(feedback_text) LIKE ?', [$term])
                ->orWhereRaw('LOWER(service_name) LIKE ?', [$term]);
        });
    }

    private function applySorting(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'oldest' => $query->oldest(),
            'highest_rating' => $query->orderByDesc('rating')->latest(),
            'lowest_rating' => $query->orderBy('rating')->latest(),
            'highest_confidence' => $query->orderByDesc('confidence_score')->latest(),
            'lowest_confidence' => $query->orderBy('confidence_score')->latest(),
            default => $query->latest(),
        };
    }

    private function whereAnyTerm(Builder $query, array $terms): Builder
    {
        foreach ($terms as $index => $term) {
            $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
            $query->{$method}('LOWER(feedback_text) LIKE ?', ['%'.$term.'%']);
        }

        return $query;
    }

    private function allowedValue(mixed $value, array $allowed): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value !== null && in_array($value, $allowed, true) ? $value : null;
    }

    private function dateValue(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
