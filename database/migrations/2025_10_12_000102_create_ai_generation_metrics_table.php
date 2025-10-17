<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generation_metrics', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->date('date');
            $table->string('model');
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedBigInteger('total_latency_ms')->default(0);
            $table->decimal('total_cost_usd', 10, 3)->default(0);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['date', 'model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_metrics');
    }
};
