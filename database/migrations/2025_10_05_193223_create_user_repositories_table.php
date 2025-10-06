<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_repositories', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('github');
            $table->string('repo_owner');
            $table->string('repo_name');
            $table->string('repo_url');
            $table->string('visibility')->default('private');
            $table->string('status')->default('created');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'provider'], 'user_repositories_user_provider_unique');
        });
    }

    public function down(): void
    {
        Schema::drop('user_repositories');
    }
};
