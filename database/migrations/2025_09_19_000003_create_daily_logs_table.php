<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('challenge_run_id')->constrained()->onDelete('cascade');
            // users table uses integer IDs today; keep FK as integer
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('day_number'); // 1..100
            $table->date('date')->nullable();
            $table->decimal('hours_coded', 4, 2)->nullable();
            $table->json('projects_worked_on')->nullable();
            $table->text('notes')->nullable();
            $table->text('learnings')->nullable();
            $table->text('challenges_faced')->nullable();
            $table->boolean('completed')->default(true);
            $table->timestamps();

            $table->unique(['challenge_run_id', 'user_id', 'day_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_logs');
    }
};
