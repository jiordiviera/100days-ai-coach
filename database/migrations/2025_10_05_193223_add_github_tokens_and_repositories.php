<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_profiles', 'github_access_token')) {
                $table->text('github_access_token')->nullable()->after('github_username');
            }

            if (! Schema::hasColumn('user_profiles', 'github_refresh_token')) {
                $table->text('github_refresh_token')->nullable()->after('github_access_token');
            }

            if (! Schema::hasColumn('user_profiles', 'github_token_expires_at')) {
                $table->timestamp('github_token_expires_at')->nullable()->after('github_refresh_token');
            }
        });

        if (! Schema::hasTable('user_repositories')) {
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
    }

    public function down(): void
    {
        if (Schema::hasTable('user_repositories')) {
            Schema::drop('user_repositories');
        }

        Schema::table('user_profiles', function (Blueprint $table): void {
            if (Schema::hasColumn('user_profiles', 'github_token_expires_at')) {
                $table->dropColumn('github_token_expires_at');
            }

            if (Schema::hasColumn('user_profiles', 'github_refresh_token')) {
                $table->dropColumn('github_refresh_token');
            }

            if (Schema::hasColumn('user_profiles', 'github_access_token')) {
                $table->dropColumn('github_access_token');
            }
        });
    }
};
