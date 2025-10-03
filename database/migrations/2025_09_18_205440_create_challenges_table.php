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
        Schema::create('challenges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->onDelete('cascade');
            $table->date('challenge_date');
            $table->text('description')->nullable();
            $table->json('projects_worked_on')->nullable();
            $table->integer('hours_coded')->default(1);
            $table->text('learnings')->nullable();
            $table->text('challenges_faced')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'challenge_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
