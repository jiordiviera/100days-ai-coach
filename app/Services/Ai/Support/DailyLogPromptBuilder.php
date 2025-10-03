<?php

namespace App\Services\Ai\Support;

use App\Models\DailyLog;
use Illuminate\Support\Str;

class DailyLogPromptBuilder
{
    public function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an AI coach helping a developer participating in the 100 Days of Code challenge. Produce:
1. A concise Markdown summary (max 120 words) with headings and bullet points when relevant.
2. Up to five lowercase tags that describe the day (topics, technologies, mindset).
3. A motivational coach tip (max 2 sentences).
4. A shareable social draft (Markdown, english, 2 short paragraphs or bullet list).

Answer strictly as JSON with keys: summary_md, tags, coach_tip, share_draft.
PROMPT;
    }

    public function buildUserPrompt(DailyLog $log): string
    {
        $lines = [];
        $lines[] = sprintf('Challenge: %s', $log->challengeRun?->title ?? '100DaysOfCode');
        $lines[] = sprintf('Day: %d', $log->day_number);

        if ($log->date) {
            $lines[] = 'Date: '.$log->date->toDateString();
        }

        $lines[] = 'Hours coded: '.($log->hours_coded ?? 0);

        if ($projects = $log->projects_worked_on ?? []) {
            $lines[] = 'Projects: '.implode(', ', $projects);
        }

        if ($log->notes) {
            $lines[] = 'Daily notes: '.Str::limit($log->notes, 500);
        }

        if ($log->learnings) {
            $lines[] = 'Learnings: '.Str::limit($log->learnings, 400);
        }

        if ($log->challenges_faced) {
            $lines[] = 'Challenges: '.Str::limit($log->challenges_faced, 400);
        }

        return implode("\n", $lines);
    }
}
