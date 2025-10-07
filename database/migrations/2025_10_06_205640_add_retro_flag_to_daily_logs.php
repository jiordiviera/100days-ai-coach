<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('daily_logs', 'retro')) {
                $table->boolean('retro')->default(false)->after('completed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('daily_logs', 'retro')) {
                $table->dropColumn('retro');
            }
        });
    }
};
