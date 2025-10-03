<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_invitations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('challenge_run_id')->constrained()->onDelete('cascade');
            $table->foreignUlid('inviter_id')->constrained('users')->onDelete('cascade');
            $table->string('email');
            $table->string('token')->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['challenge_run_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_invitations');
    }
};
