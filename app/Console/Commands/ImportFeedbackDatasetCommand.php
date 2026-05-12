<?php

namespace App\Console\Commands;

use App\Models\FeedbackSentiment;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use SplFileObject;

class ImportFeedbackDatasetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback:import-dataset
        {path=python_nlp_api/sample_feedback_dataset.csv : CSV path with feedback and sentiment columns}
        {--truncate : Delete existing feedback_sentiments before import}
        {--limit=0 : Maximum rows to import, 0 imports all rows}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import labeled feedback CSV rows into the dashboard database.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (! is_file($path)) {
            $this->error("Dataset not found: {$path}");

            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            FeedbackSentiment::query()->delete();
        }

        $limit = max(0, (int) $this->option('limit'));
        $rows = $this->readRows($path);
        $imported = 0;

        foreach ($rows as $index => $row) {
            if ($limit > 0 && $imported >= $limit) {
                break;
            }

            $sentiment = $this->normalizeSentiment($row['sentiment'] ?? '');
            $feedbackText = trim((string) ($row['feedback'] ?? ''));

            if ($feedbackText === '' || $sentiment === null) {
                continue;
            }

            FeedbackSentiment::query()->create([
                'customer_name' => $this->customerName($index),
                'service_name' => $this->serviceName($feedbackText, $index),
                'rating' => $this->rating($sentiment, $index),
                'feedback_text' => $feedbackText,
                'predicted_sentiment' => $sentiment,
                'confidence_score' => $this->confidence($sentiment, $index),
                'created_at' => $this->createdAt($index),
                'updated_at' => now(),
            ]);

            $imported++;
        }

        $this->info("Imported {$imported} feedback rows.");
        $this->table(
            ['Sentiment', 'Count'],
            FeedbackSentiment::query()
                ->selectRaw('predicted_sentiment, COUNT(*) as total')
                ->groupBy('predicted_sentiment')
                ->orderBy('predicted_sentiment')
                ->get()
                ->map(fn ($row) => [$row->predicted_sentiment, $row->total])
                ->all()
        );

        return self::SUCCESS;
    }

    private function readRows(string $path): array
    {
        $file = new SplFileObject($path);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $headers = [];
        $rows = [];

        foreach ($file as $lineNumber => $line) {
            if ($line === [null] || $line === false) {
                continue;
            }

            if ($lineNumber === 0) {
                $headers = array_map(function ($header) {
                    $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);

                    return strtolower(trim($header, " \t\n\r\0\x0B\"'"));
                }, $line);
                continue;
            }

            if (count($line) !== count($headers)) {
                continue;
            }

            $rows[] = array_combine($headers, $line);
        }

        return $rows;
    }

    private function normalizeSentiment(string $sentiment): ?string
    {
        return match (strtolower(trim($sentiment))) {
            'positive', 'pos', 'good', '1' => 'Positive',
            'neutral', 'neu', 'okay', 'ok', '0' => 'Neutral',
            'negative', 'neg', 'bad', '-1' => 'Negative',
            default => null,
        };
    }

    private function customerName(int $index): string
    {
        $names = [
            'Maria Santos', 'Juan Dela Cruz', 'Ana Reyes', 'Mark Garcia', 'Liza Cruz',
            'Carlo Mendoza', 'Jessa Ramos', 'Paolo Villanueva', 'Grace Bautista', 'Rica Navarro',
        ];

        return $names[$index % count($names)].' #'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
    }

    private function serviceName(string $feedbackText, int $index): string
    {
        $services = [
            'Massage Therapy', 'Aromatherapy Massage', 'Hot Stone Massage', 'Hilot Massage',
            'Foot Spa', 'Facial Treatment', 'Body Scrub', 'Spa Package', 'Waxing Service', 'Ventosa Service',
        ];

        $lower = strtolower($feedbackText);

        foreach ($services as $service) {
            $keyword = strtolower(str_replace([' Therapy', ' Treatment', ' Service'], '', $service));

            if (str_contains($lower, $keyword)) {
                return $service;
            }
        }

        return $services[$index % count($services)];
    }

    private function rating(string $sentiment, int $index): int
    {
        return match ($sentiment) {
            'Positive' => [5, 5, 4, 5][$index % 4],
            'Neutral' => [3, 3, 4, 2][$index % 4],
            'Negative' => [1, 2, 1, 2][$index % 4],
        };
    }

    private function confidence(string $sentiment, int $index): float
    {
        $base = match ($sentiment) {
            'Positive' => 0.88,
            'Neutral' => 0.82,
            'Negative' => 0.86,
        };

        return round(min(0.98, $base + (($index % 9) * 0.01)), 4);
    }

    private function createdAt(int $index): Carbon
    {
        return now()
            ->subDays($index % 30)
            ->subHours($index % 12)
            ->subMinutes(($index * 7) % 60);
    }
}
