<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedbackSentimentRequest;
use App\Models\FeedbackSentiment;
use App\Services\SentimentPredictionService;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreFeedbackSentimentRequest $request): RedirectResponse
    {
        try {
            $prediction = $this->sentimentService->predict($request->validated('feedback_text'));
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['feedback_text' => $exception->getMessage()]);
        }

        $feedback = FeedbackSentiment::create([
            ...$request->validated(),
            'predicted_sentiment' => $prediction['sentiment'],
            'confidence_score' => $prediction['confidence'],
        ]);

        return redirect()
            ->route('feedback.thank-you', $feedback)
            ->with('status', 'Feedback submitted successfully.');
    }

    public function thankYou(FeedbackSentiment $feedbackSentiment): View
    {
        return view('feedback.thank-you', ['feedback' => $feedbackSentiment]);
    }

    public function index(): View
    {
        $feedback = FeedbackSentiment::query()
            ->latest()
            ->paginate(12);

        return view('feedback.index', compact('feedback'));
    }

    public function show(FeedbackSentiment $feedbackSentiment): View
    {
        return view('feedback.show', ['feedback' => $feedbackSentiment]);
    }

    public function edit(FeedbackSentiment $feedbackSentiment): View
    {
        return view('feedback.edit', ['feedback' => $feedbackSentiment]);
    }

    public function update(StoreFeedbackSentimentRequest $request, FeedbackSentiment $feedbackSentiment): RedirectResponse
    {
        try {
            $prediction = $this->sentimentService->predict($request->validated('feedback_text'));
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['feedback_text' => $exception->getMessage()]);
        }

        $feedbackSentiment->update([
            ...$request->validated(),
            'predicted_sentiment' => $prediction['sentiment'],
            'confidence_score' => $prediction['confidence'],
        ]);

        return redirect()
            ->route('feedback.index')
            ->with('status', 'Feedback updated and sentiment recalculated.');
    }

    public function destroy(FeedbackSentiment $feedbackSentiment): RedirectResponse
    {
        $feedbackSentiment->delete();

        return redirect()
            ->route('feedback.index')
            ->with('status', 'Feedback deleted.');
    }
}
