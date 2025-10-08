<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->boolean('is_public')->default(false)->after('username');
        });

        Schema::table('challenge_runs', function (Blueprint $table): void {
            $table->string('public_slug')->nullable()->unique()->after('public_join_code');
        });

        DB::table('challenge_runs')
            ->select(['id', 'title'])
            ->orderBy('created_at')
            ->chunkById(100, function ($runs): void {
                foreach ($runs as $run) {
                    $slug = self::generateSlug($run->title);

                    DB::table('challenge_runs')
                        ->where('id', $run->id)
                        ->update(['public_slug' => $slug]);
                }
            }, 'id', 'id');
    }

    public function down(): void
    {
        Schema::table('challenge_runs', function (Blueprint $table): void {
            $table->dropColumn('public_slug');
        });

        Schema::table('user_profiles', function (Blueprint $table): void {
            $table->dropColumn('is_public');
        });
    }

    protected static function generateSlug(?string $title): string
    {
        $base = Str::of($title ?? 'challenge')
            ->slug('-')
            ->limit(48, '')
            ->trim('-')
            ->value() ?: 'challenge';

        return Str::lower(sprintf('%s-%s', $base, Str::random(6)));
    }
};
