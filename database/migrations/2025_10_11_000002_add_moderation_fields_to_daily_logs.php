<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table): void {
            $table->timestamp('hidden_at')->nullable()->after('public_token');
            $table->foreignUlid('moderated_by_id')
                ->nullable()
                ->after('hidden_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('moderation_notes')->nullable()->after('moderated_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table): void {
            $table->dropForeign(['moderated_by_id']);
            $table->dropColumn(['hidden_at', 'moderated_by_id', 'moderation_notes']);
        });
    }
};
