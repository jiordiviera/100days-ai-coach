<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'notifications_outbox_user_status_schedule_index';

    public function up(): void
    {
        Schema::create('notifications_outbox', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('channel');
            $table->json('payload');
            $table->timestampTz('scheduled_at')->nullable();
            $table->timestampTz('sent_at')->nullable();
            $table->string('status')->default('queued');
            $table->text('error')->nullable();
            $table->timestampsTz();

            $table->index(['user_id', 'status', 'scheduled_at'], self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications_outbox');
    }
};
