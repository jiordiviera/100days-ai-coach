<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_runs', function (Blueprint $table) {
            $table->string('public_join_code')->nullable()->after('is_public');
        });
    }

    public function down(): void
    {
        Schema::table('challenge_runs', function (Blueprint $table) {
            $table->dropColumn(['public_join_code']);
        });
    }
};
