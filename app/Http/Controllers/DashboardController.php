<?php

namespace App\Http\Controllers;

use App\Models\FeedbackSentiment;
use Carbon\CarbonPeriod;
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

    public function __invoke(): View
    {
        $sentiments = ['Positive', 'Neutral', 'Negative'];

        $counts = FeedbackSentiment::query()
            ->select('predicted_sentiment', DB::raw('COUNT(*) as total'))
            ->groupBy('predicted_sentiment')
            ->pluck('total', 'predicted_sentiment');

        $summary = collect($sentiments)
            ->mapWithKeys(fn (string $sentiment) => [$sentiment => (int) ($counts[$sentiment] ?? 0)])
            ->all();

        $period = CarbonPeriod::create(now()->subDays(13)->startOfDay(), now()->startOfDay());
        $labels = collect($period)->map(fn (Carbon $date) => $date->format('M d'))->values();

        $trendRows = FeedbackSentiment::query()
            ->selectRaw('DATE(created_at) as review_date, predicted_sentiment, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
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

        $latestReviews = FeedbackSentiment::query()
            ->latest()
            ->limit(8)
            ->get();

        $negativeReviews = FeedbackSentiment::query()
            ->where('predicted_sentiment', 'Negative')
            ->latest()
            ->limit(250)
            ->get(['feedback_text']);

        $allTrendReviews = FeedbackSentiment::query()
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['feedback_text', 'predicted_sentiment', 'created_at']);

        $taglishTrend = collect($sentiments)->mapWithKeys(function (string $sentiment) use ($period, $allTrendReviews) {
            $data = collect($period)->map(function (Carbon $date) use ($sentiment, $allTrendReviews) {
                return $allTrendReviews
                    ->filter(fn (FeedbackSentiment $review) => $review->predicted_sentiment === $sentiment)
                    ->filter(fn (FeedbackSentiment $review) => $review->created_at->isSameDay($date))
                    ->filter(fn (FeedbackSentiment $review) => $this->isTaglishText($review->feedback_text))
                    ->count();
            });

            return [$sentiment => $data->values()];
        });

        $ratioTotal = $summary['Positive'] + $summary['Negative'];
        $positiveNegativeRatio = $summary['Negative'] > 0
            ? round($summary['Positive'] / $summary['Negative'], 2).':1'
            : ($summary['Positive'] > 0 ? $summary['Positive'].':0' : '0:0');

        return view('dashboard.index', [
            'summary' => $summary,
            'positiveNegativeRatio' => $positiveNegativeRatio,
            'positiveShare' => $ratioTotal > 0 ? round(($summary['Positive'] / $ratioTotal) * 100, 1) : 0,
            'pieLabels' => $sentiments,
            'pieData' => array_values($summary),
            'trendLabels' => $labels,
            'trendData' => $trend,
            'taglishTrendData' => $taglishTrend,
            'negativeTaglishWords' => $this->topNegativeTaglishWords($negativeReviews),
            'frequentComplaints' => $this->frequentComplaints($negativeReviews),
            'latestReviews' => $latestReviews,
        ]);
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
        preg_match_all('/[a-zñ]+/i', strtolower($text), $matches);

        return $matches[0] ?? [];
    }
}
