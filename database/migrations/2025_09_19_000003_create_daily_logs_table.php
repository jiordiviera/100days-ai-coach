<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('challenge_run_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('day_number');
            $table->date('date')->nullable();
            $table->decimal('hours_coded', 4, 2)->nullable();
            $table->json('projects_worked_on')->nullable();
            $table->text('notes')->nullable();
            $table->text('learnings')->nullable();
            $table->text('challenges_faced')->nullable();
            $table->boolean('completed')->default(true);
            $table->text('summary_md')->nullable();
            $table->json('tags')->nullable();
            $table->text('coach_tip')->nullable();
            $table->text('share_draft')->nullable();
            $table->string('ai_model', 64)->nullable();
            $table->integer('ai_latency_ms')->nullable();
            $table->decimal('ai_cost_usd', 6, 3)->default(0)->nullable();
            $table->char('public_token', 26)->nullable();
            $table->json('wakatime_summary')->nullable();
            $table->timestamp('wakatime_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['challenge_run_id', 'user_id', 'day_number'], 'daily_logs_run_user_day_unique_v2');
            $table->unique('public_token', 'daily_logs_public_token_unique');
            $table->index(['challenge_run_id', 'user_id', 'created_at'], 'daily_logs_challenge_run_user_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
