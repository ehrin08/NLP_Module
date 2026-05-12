<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SentimentPredictionService
{
    public function predict(string $text): array
    {
        $endpoint = config('services.sentiment.url', env('SENTIMENT_API_URL', 'http://127.0.0.1:5000/predict'));

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->asJson()
                ->post($endpoint, ['text' => $text]);
        } catch (\Throwable $exception) {
            Log::error('Sentiment API connection failed.', ['message' => $exception->getMessage()]);

            throw new RuntimeException('Sentiment service is unavailable. Start the Flask API and try again.');
        }

        if (! $response->successful()) {
            Log::warning('Sentiment API returned an error.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Sentiment service returned an invalid response.');
        }

        $payload = $response->json();
        $sentiment = $payload['sentiment'] ?? null;
        $confidence = $payload['confidence'] ?? null;

        if (! in_array($sentiment, ['Positive', 'Neutral', 'Negative'], true) || ! is_numeric($confidence)) {
            throw new RuntimeException('Sentiment service response is missing prediction data.');
        }

        return [
            'sentiment' => $sentiment,
            'confidence' => max(0, min(1, (float) $confidence)),
        ];
    }
}
