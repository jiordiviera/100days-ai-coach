<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('join_reason')->nullable();
            $table->string('focus_area')->nullable();
            $table->json('preferences')->nullable();
            $table->json('social_links')->nullable(); // { "github":"...", "twitter":"...", "linkedin":"...", "website":"..." }
            $table->string('avatar_url')->nullable();
            $table->string('bio', 160)->nullable();
            $table->string('username', 32)->nullable()->unique();
            $table->string('github_id')->nullable()->unique();
            $table->string('github_username')->nullable();
            $table->text('wakatime_api_key')->nullable();
            $table->json('wakatime_settings')->nullable();

            $table->text('github_refresh_token')->nullable();
            $table->text('github_access_token')->nullable();
            $table->timestamp('github_token_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
