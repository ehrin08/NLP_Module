<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackSentimentRequest;
use App\Models\FeedbackSentiment;
use App\Services\FeedbackFilterService;
use App\Services\SentimentPredictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class FeedbackController extends Controller
{
    public function __construct(private readonly SentimentPredictionService $sentimentService)
    {
    }

    public function create(): View
    {
        return view('feedback.create');
    }

    public function store(StoreFeedbackSentimentRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $prediction = $this->sentimentService->predict($request->validated('feedback_text'));
        } catch (RuntimeException $exception) {
            if ($this->isAsync($request)) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => ['feedback_text' => [$exception->getMessage()]],
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['feedback_text' => $exception->getMessage()]);
        }

        $feedback = FeedbackSentiment::create([
            ...$request->validated(),
            'predicted_sentiment' => $prediction['sentiment'],
            'confidence_score' => $prediction['confidence'],
        ]);

        if ($this->isAsync($request)) {
            return response()->json([
                'message' => 'Feedback submitted successfully.',
                'feedback' => $feedback,
            ]);
        }

        return redirect()
            ->route('feedback.thank-you', $feedback)
            ->with('status', 'Feedback submitted successfully.');
    }

    public function thankYou(FeedbackSentiment $feedbackSentiment): View
    {
        return view('feedback.thank-you', ['feedback' => $feedbackSentiment]);
    }

    public function index(Request $request, FeedbackFilterService $filters): View|JsonResponse
    {
        $activeFilters = $filters->fromRequest($request);
        $feedback = $filters->apply(FeedbackSentiment::query(), $activeFilters)
            ->paginate(12)
            ->withQueryString();

        $payload = [
            'feedback' => $feedback,
            'filters' => $activeFilters,
            'filterOptions' => [
                'sentiments' => FeedbackFilterService::SENTIMENTS,
                'datePresets' => FeedbackFilterService::DATE_PRESETS,
                'services' => FeedbackSentiment::query()->distinct()->orderBy('service_name')->pluck('service_name'),
                'sorts' => FeedbackFilterService::SORTS,
                'languages' => FeedbackFilterService::LANGUAGES,
            ],
        ];

        if ($this->isAsync($request)) {
            return response()->json([
                'html' => view('feedback.partials.table', $payload)->render(),
                'url' => $request->fullUrl(),
            ]);
        }

        return view('feedback.index', $payload);
    }

    public function show(FeedbackSentiment $feedbackSentiment): View
    {
        return view('feedback.show', ['feedback' => $feedbackSentiment]);
    }

    public function edit(FeedbackSentiment $feedbackSentiment): View
    {
        return view('feedback.edit', ['feedback' => $feedbackSentiment]);
    }

    public function update(StoreFeedbackSentimentRequest $request, FeedbackSentiment $feedbackSentiment): RedirectResponse|JsonResponse
    {
        try {
            $prediction = $this->sentimentService->predict($request->validated('feedback_text'));
        } catch (RuntimeException $exception) {
            if ($this->isAsync($request)) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'errors' => ['feedback_text' => [$exception->getMessage()]],
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['feedback_text' => $exception->getMessage()]);
        }

        $feedbackSentiment->update([
            ...$request->validated(),
            'predicted_sentiment' => $prediction['sentiment'],
            'confidence_score' => $prediction['confidence'],
        ]);

        if ($this->isAsync($request)) {
            return response()->json([
                'message' => 'Feedback updated and sentiment recalculated.',
                'feedback' => $feedbackSentiment->fresh(),
            ]);
        }

        return redirect()
            ->route('feedback.index')
            ->with('status', 'Feedback updated and sentiment recalculated.');
    }

    public function destroy(Request $request, FeedbackSentiment $feedbackSentiment): RedirectResponse|JsonResponse
    {
        $feedbackSentiment->delete();

        if ($this->isAsync($request)) {
            return response()->json([
                'message' => 'Feedback deleted.',
            ]);
        }

        return redirect()
            ->route('feedback.index')
            ->with('status', 'Feedback deleted.');
    }

    private function isAsync(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->header('X-Requested-With') === 'XMLHttpRequest';
    }
}
