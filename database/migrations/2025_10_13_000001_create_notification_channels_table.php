<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_channels', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->ulidMorphs('notifiable');
            $table->string('channel');
            $table->string('value');
            $table->string('language', 8)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('last_failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['notifiable_type', 'notifiable_id', 'channel', 'value'], 'notification_channels_unique_value');
            $table->index(['channel', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_channels');
    }
};
