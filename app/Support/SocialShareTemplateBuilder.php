<?php

namespace App\Support;

use App\Models\DailyLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SocialShareTemplateBuilder
{
    /**
     * Build social share templates for a given daily log.
     *
     * @param  array{summary?: string, tags?: array<int, string>, share_draft?: string}|array<string, mixed>  $context
     * @return array<string, string>
     */
    public function build(DailyLog $log, array $context = []): array
    {
        $log->loadMissing([
            'challengeRun',
            'user.profile',
        ]);

        $dayLabel = $this->formatDayLabel($log);
        $headline = $this->buildHeadline($log, $context);
        $highlights = $this->extractHighlights($log, $context);
        $hashtags = $this->resolveHashtags($log);
        $hours = $this->formatHours($log);

        $templates = [
            'linkedin' => $this->buildLinkedInTemplate($dayLabel, $headline, $highlights, $hashtags, $hours),
            'x' => $this->buildXTemplate($dayLabel, $headline, $highlights, $hashtags, $hours),
        ];

        return array_filter($templates);
    }

    /**
     * @param  array<int, string>  $highlights
     * @param  array<int, string>  $hashtags
     */
    protected function buildLinkedInTemplate(string $dayLabel, string $headline, array $highlights, array $hashtags, ?string $hours): ?string
    {
        $lines = [];

        $lines[] = sprintf('%s — %s', $dayLabel, $headline);

        if ($hours) {
            $lines[] = "⏱️ {$hours} de code aujourd’hui";
        }

        if (! empty($highlights)) {
            $lines[] = '';
            $lines[] = '🚀 Points forts';
            foreach ($highlights as $highlight) {
                $lines[] = '• '.$highlight;
            }
        }

        $lines[] = '';
        $lines[] = 'On garde le rythme 💪';

        if (! empty($hashtags)) {
            $lines[] = '';
            $lines[] = implode(' ', $hashtags);
        }

        $content = trim(preg_replace("/(\r?\n){3,}/", "\n\n", implode("\n", array_filter($lines, fn ($line) => $line !== null && $line !== ''))));

        return $content !== '' ? $content : null;
    }

    /**
     * @param  array<int, string>  $highlights
     * @param  array<int, string>  $hashtags
     */
    protected function buildXTemplate(string $dayLabel, string $headline, array $highlights, array $hashtags, ?string $hours): ?string
    {
        $lines = [];
        $lines[] = sprintf('%s : %s', str_replace('Jour', 'Day', $dayLabel), Str::limit($headline, 120));

        $highlightEmojis = ['🔥', '✨', '🛠️'];

        foreach (array_values($highlights) as $index => $highlight) {
            if ($index >= 2) {
                break;
            }

            $emoji = $highlightEmojis[$index] ?? '•';
            $lines[] = sprintf('%s %s', $emoji, Str::limit($highlight, 70));
        }

        if ($hours) {
            $lines[] = "⏱️ {$hours}";
        }

        if (! empty($hashtags)) {
            $lines[] = implode(' ', $hashtags);
        }

        $content = trim(implode("\n", array_filter($lines, fn ($line) => $line !== null && $line !== '')));

        if ($content === '') {
            return null;
        }

        // Ensure compliance with X character limits.
        if (Str::length($content) > 270) {
            $content = Str::limit($content, 270, '');
        }

        return $content;
    }

    protected function buildHeadline(DailyLog $log, array $context): string
    {
        $summary = Str::of($context['summary'] ?? $log->summary_md ?? '')
            ->stripTags()
            ->squish();

        if ($summary->isNotEmpty()) {
            return Str::limit($summary->value(), 120, '');
        }

        $aiDraft = Str::of($context['share_draft'] ?? $log->share_draft ?? '')
            ->stripTags()
            ->squish();

        if ($aiDraft->isNotEmpty()) {
            return Str::limit($aiDraft->value(), 120, '');
        }

        $notes = Str::of($log->notes ?? '')
            ->stripTags()
            ->squish();

        if ($notes->isNotEmpty()) {
            return Str::limit($notes->value(), 120, '');
        }

        return 'Je poursuis mon challenge #100DaysOfCode';
    }

    /**
     * @return array<int, string>
     */
    protected function extractHighlights(DailyLog $log, array $context): array
    {
        $tags = collect($context['tags'] ?? $log->tags ?? [])
            ->map(fn ($tag) => Str::of((string) $tag)->stripTags()->squish()->value())
            ->filter()
            ->unique()
            ->values();

        if ($tags->isNotEmpty()) {
            return $tags->map(fn ($tag) => Str::limit($tag, 100, ''))->take(3)->values()->all();
        }

        $notes = $this->splitIntoHighlights($log->notes);
        if ($notes->isNotEmpty()) {
            return $notes->take(3)->map(fn ($line) => Str::limit($line, 100, ''))->values()->all();
        }

        $learnings = $this->splitIntoHighlights($log->learnings);
        if ($learnings->isNotEmpty()) {
            return $learnings->take(3)->map(fn ($line) => Str::limit($line, 100, ''))->values()->all();
        }

        if ($log->projects_worked_on) {
            return collect($log->projects_worked_on)
                ->map(fn ($project) => 'Focus projet : '.Str::of((string) $project)->squish()->limit(80))
                ->take(3)
                ->values()
                ->all();
        }

        if ($log->hours_coded) {
            return ['Temps consacré : '.$this->formatHours($log)];
        }

        return [];
    }

    protected function splitIntoHighlights(?string $text): Collection
    {
        if (blank($text)) {
            return collect();
        }

        $normalized = Str::of($text)
            ->replace(['•', '*', '- '], "\n")
            ->replaceMatches('/\r\n|\r/', "\n")
            ->explode("\n")
            ->map(fn ($line) => Str::of($line)->stripTags()->squish()->value())
            ->filter()
            ->values();

        if ($normalized->isEmpty()) {
            $sentences = preg_split('/(?<=[.!?])\s+/', Str::of($text)->stripTags()->squish()->value()) ?: [];

            return collect($sentences)
                ->map(fn ($sentence) => trim((string) $sentence))
                ->filter();
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    protected function resolveHashtags(DailyLog $log): array
    {
        $defaults = ['#100DaysOfCode', '#buildinpublic'];

        $custom = collect();

        if ($log->relationLoaded('user') || $log->relationLoaded('user.profile') || $log->user) {
            $profile = $log->user?->profile;
            $preferences = $profile?->preferences ?? [];

            $custom = collect(data_get($preferences, 'social.share_hashtags', []))
                ->map(fn ($tag) => Str::of((string) $tag)->squish()->value());
        }

        return $custom
            ->merge($defaults)
            ->map(function (string $tag) {
                $body = preg_replace('/[^A-Za-z0-9_]/', '', ltrim($tag, "# \t\n\r\0\x0B"));

                if (! $body) {
                    return null;
                }

                return '#'.$body;
            })
            ->filter()
            ->unique()
            ->take(6)
            ->values()
            ->all();
    }

    protected function formatDayLabel(DailyLog $log): string
    {
        $target = (int) ($log->challengeRun?->target_days ?? $log->day_number ?? 100);
        $day = (int) ($log->day_number ?? 1);

        return sprintf('Jour %d/%d', max(1, $day), max($day, $target));
    }

    protected function formatHours(DailyLog $log): ?string
    {
        if (! $log->hours_coded) {
            return null;
        }

        return rtrim(rtrim(number_format((float) $log->hours_coded, 1, '.', ''), '0'), '.').'h';
    }
}
