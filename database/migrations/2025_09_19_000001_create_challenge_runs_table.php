<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            // users table uses integer IDs today; keep FK as integer
            $table->foreignUlid('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->unsignedSmallInteger('target_days')->default(100);
            $table->string('status')->default('active'); // draft|active|paused|completed
            $table->boolean('is_public')->default(false);
            $table->string('public_join_code')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_runs');
    }
};
