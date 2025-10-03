<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_participants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('challenge_run_id')->constrained()->onDelete('cascade');
            // users table uses integer IDs today; keep FK as integer
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['challenge_run_id', 'user_id'], 'challenge_participants_run_user_unique_v2');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_participants');
    }
};
