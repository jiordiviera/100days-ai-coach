<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_profiles', 'wakatime_api_key')) {
                $table->text('wakatime_api_key')->nullable()->after('github_username');
            }

            if (! Schema::hasColumn('user_profiles', 'wakatime_settings')) {
                $table->json('wakatime_settings')->nullable()->after('wakatime_api_key');
            }
        });

        Schema::table('daily_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('daily_logs', 'wakatime_summary')) {
                $table->json('wakatime_summary')->nullable()->after('projects_worked_on');
            }

            if (! Schema::hasColumn('daily_logs', 'wakatime_synced_at')) {
                $table->timestamp('wakatime_synced_at')->nullable()->after('wakatime_summary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('daily_logs', 'wakatime_synced_at')) {
                $table->dropColumn('wakatime_synced_at');
            }

            if (Schema::hasColumn('daily_logs', 'wakatime_summary')) {
                $table->dropColumn('wakatime_summary');
            }
        });

        Schema::table('user_profiles', function (Blueprint $table): void {
            if (Schema::hasColumn('user_profiles', 'wakatime_settings')) {
                $table->dropColumn('wakatime_settings');
            }

            if (Schema::hasColumn('user_profiles', 'wakatime_api_key')) {
                $table->dropColumn('wakatime_api_key');
            }
        });
    }
};
