<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->json('ai_metadata')->nullable()->after('ai_cost_usd');
            $table->timestamp('public_token_expires_at')->nullable()->after('public_token');
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table) {
            $table->dropColumn(['ai_metadata', 'public_token_expires_at']);
        });
    }
};
