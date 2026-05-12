<?php

namespace App\Http\Controllers;

use App\Models\FeedbackSentiment;
use App\Services\FeedbackFilterService;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const TAGLISH_TERMS = [
        'ako', 'kami', 'yung', 'ang', 'ng', 'sa', 'naman', 'talaga', 'hindi', 'di', 'wala', 'walang',
        'sulit', 'bait', 'mabait', 'matagal', 'mainit', 'malamig', 'maingay', 'presyo', 'pwede',
        'puwede', 'sakto', 'lang', 'masakit', 'bitin', 'naasikaso', 'magaling', 'malinis',
    ];

    private const ANALYTICS_STOPWORDS = [
        'ang', 'mga', 'yung', 'naman', 'kasi', 'pero', 'talaga', 'saka', 'lang', 'po', 'opo',
        'the', 'and', 'was', 'were', 'for', 'with', 'this', 'that', 'service', 'spa', 'today',
        'ako', 'kami', 'ng', 'sa', 'na', 'pa', 'my', 'our', 'very', 'too',
    ];

    private const COMPLAINT_CATEGORIES = [
        'Waiting Time' => ['matagal', 'tagal', 'waiting', 'waited', 'late', 'delay', 'delayed', 'naasikaso'],
        'Price / Value' => ['mahal', 'presyo', 'price', 'expensive', 'worth', 'sulit', 'overpriced'],
        'Room Comfort' => ['mainit', 'malamig', 'maingay', 'noisy', 'room', 'temperature', 'ambiance'],
        'Cleanliness' => ['malinis', 'dirty', 'towel', 'towels', 'linen', 'linens', 'smell', 'smelled'],
        'Staff / Therapist' => ['staff', 'therapist', 'receptionist', 'rude', 'ignored', 'impatient', 'friendly'],
        'Service Quality' => ['rushed', 'bitin', 'pressure', 'poor', 'bad', 'masakit', 'careless', 'relaxing'],
    ];

    public function __invoke(Request $request, FeedbackFilterService $filters): View|JsonResponse
    {
        $activeFilters = $filters->fromRequest($request);
        $sentiments = ['Positive', 'Neutral', 'Negative'];
        $baseQuery = $filters->apply(FeedbackSentiment::query(), $activeFilters, false);
        $latestLimit = max(4, min(20, (int) $request->integer('latest_limit', 8)));

        $counts = (clone $baseQuery)
            ->select('predicted_sentiment', DB::raw('COUNT(*) as total'))
            ->groupBy('predicted_sentiment')
            ->pluck('total', 'predicted_sentiment');

        $summary = collect($sentiments)
            ->mapWithKeys(fn (string $sentiment) => [$sentiment => (int) ($counts[$sentiment] ?? 0)])
            ->all();

        [$trendStart, $trendEnd] = $filters->trendRange($activeFilters);
        $period = CarbonPeriod::create($trendStart->copy()->startOfDay(), $trendEnd->copy()->startOfDay());
        $labels = collect($period)->map(fn (Carbon $date) => $date->format('M d'))->values();

        $trendRows = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as review_date, predicted_sentiment, COUNT(*) as total')
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->groupBy('review_date', 'predicted_sentiment')
            ->orderBy('review_date')
            ->get()
            ->groupBy(fn ($row) => $row->review_date.'|'.$row->predicted_sentiment);

        $trend = collect($sentiments)->mapWithKeys(function (string $sentiment) use ($period, $trendRows) {
            $data = collect($period)->map(function (Carbon $date) use ($sentiment, $trendRows) {
                $key = $date->format('Y-m-d').'|'.$sentiment;

                return (int) optional($trendRows->get($key)?->first())->total;
            });

            return [$sentiment => $data->values()];
        });

        $latestReviews = $filters->apply(FeedbackSentiment::query(), $activeFilters)
            ->limit($latestLimit)
            ->get();

        $negativeReviews = (clone $baseQuery)
            ->where('predicted_sentiment', 'Negative')
            ->latest()
            ->limit(250)
            ->get(['feedback_text']);

        $languageTrend = $activeFilters['language'] ?: 'taglish';
        $languageTrendRows = $filters->applyLanguage((clone $baseQuery), $languageTrend)
            ->selectRaw('DATE(created_at) as review_date, predicted_sentiment, COUNT(*) as total')
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->groupBy('review_date', 'predicted_sentiment')
            ->orderBy('review_date')
            ->get()
            ->groupBy(fn ($row) => $row->review_date.'|'.$row->predicted_sentiment);

        $languageTrendData = collect($sentiments)->mapWithKeys(function (string $sentiment) use ($period, $languageTrendRows) {
            $data = collect($period)->map(function (Carbon $date) use ($sentiment, $languageTrendRows) {
                $key = $date->format('Y-m-d').'|'.$sentiment;

                return (int) optional($languageTrendRows->get($key)?->first())->total;
            });

            return [$sentiment => $data->values()];
        });

        $ratioTotal = $summary['Positive'] + $summary['Negative'];
        $positiveNegativeRatio = $summary['Negative'] > 0
            ? round($summary['Positive'] / $summary['Negative'], 2).':1'
            : ($summary['Positive'] > 0 ? $summary['Positive'].':0' : '0:0');

        $payload = [
            'summary' => $summary,
            'positiveNegativeRatio' => $positiveNegativeRatio,
            'positiveShare' => $ratioTotal > 0 ? round(($summary['Positive'] / $ratioTotal) * 100, 1) : 0,
            'pieLabels' => $sentiments,
            'pieData' => array_values($summary),
            'trendLabels' => $labels,
            'trendData' => $trend,
            'taglishTrendData' => $languageTrendData,
            'languageTrendTitle' => ($activeFilters['language'] ? FeedbackFilterService::LANGUAGES[$activeFilters['language']] : 'Taglish Reviews').' Sentiment Trend',
            'negativeTaglishWords' => $this->topNegativeTaglishWords($negativeReviews),
            'frequentComplaints' => $this->frequentComplaints($negativeReviews),
            'latestReviews' => $latestReviews,
            'filters' => $activeFilters,
            'filterOptions' => [
                'sentiments' => FeedbackFilterService::SENTIMENTS,
                'datePresets' => FeedbackFilterService::DATE_PRESETS,
                'services' => FeedbackSentiment::query()->distinct()->orderBy('service_name')->pluck('service_name'),
                'sorts' => FeedbackFilterService::SORTS,
                'languages' => FeedbackFilterService::LANGUAGES,
            ],
            'latestLimit' => $latestLimit,
        ];

        if ($this->isAsync($request)) {
            return response()->json([
                'html' => view('dashboard.partials.content', $payload)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('dashboard.index', $payload);
    }

    private function topNegativeTaglishWords($negativeReviews): array
    {
        return $negativeReviews
            ->flatMap(fn (FeedbackSentiment $review) => $this->tokenize($review->feedback_text))
            ->reject(fn (string $token) => in_array($token, self::ANALYTICS_STOPWORDS, true) || strlen($token) <= 2)
            ->countBy()
            ->sortDesc()
            ->take(10)
            ->map(fn (int $count, string $word) => ['word' => $word, 'count' => $count])
            ->values()
            ->all();
    }

    private function frequentComplaints($negativeReviews): array
    {
        $feedback = $negativeReviews
            ->pluck('feedback_text')
            ->map(fn (string $text) => strtolower($text))
            ->all();

        return collect(self::COMPLAINT_CATEGORIES)
            ->map(function (array $keywords, string $category) use ($feedback) {
                $count = collect($feedback)
                    ->filter(function (string $text) use ($keywords) {
                        return collect($keywords)->contains(fn (string $keyword) => str_contains($text, $keyword));
                    })
                    ->count();

                return ['category' => $category, 'count' => $count];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    private function isTaglishText(string $text): bool
    {
        $tokens = $this->tokenize($text);

        return collect($tokens)->intersect(self::TAGLISH_TERMS)->isNotEmpty();
    }

    private function tokenize(string $text): array
    {
        preg_match_all('/[a-zÃ±]+/i', strtolower($text), $matches);

        return $matches[0] ?? [];
    }

    private function isAsync(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }
}
