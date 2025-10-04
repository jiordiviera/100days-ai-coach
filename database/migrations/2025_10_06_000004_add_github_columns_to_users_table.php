<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            if (! Schema::hasColumn('user_profiles', 'github_id')) {
                $table->string('github_id')->nullable()->unique();
            }

            if (! Schema::hasColumn('user_profiles', 'github_username')) {
                $table->string('github_username')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            if (Schema::hasColumn('user_profiles', 'github_id')) {
                $table->dropColumn('github_id');
            }

            if (Schema::hasColumn('user_profiles', 'github_username')) {
                $table->dropColumn('github_username');
            }
        });
    }
};
