<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('feedback_sentiments', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('service_name');
            $table->unsignedTinyInteger('rating');
            $table->text('feedback_text');
            $table->enum('predicted_sentiment', ['Positive', 'Neutral', 'Negative'])->index();
            $table->decimal('confidence_score', 5, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_sentiments');
    }
};
