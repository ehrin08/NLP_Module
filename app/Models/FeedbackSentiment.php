<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackSentiment extends Model
{
    protected $fillable = [
        'customer_name',
        'service_name',
        'rating',
        'feedback_text',
        'predicted_sentiment',
        'confidence_score',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'confidence_score' => 'decimal:4',
        ];
    }
}
