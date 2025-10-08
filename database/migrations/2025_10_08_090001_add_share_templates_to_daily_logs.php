<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table): void {
            $table->json('share_templates')->nullable()->after('share_draft');
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table): void {
            $table->dropColumn('share_templates');
        });
    }
};
