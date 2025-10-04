<?php

namespace App\Livewire\Page;

use App\Jobs\GenerateDailyLogInsights;
use App\Models\ChallengeInvitation;
use App\Models\ChallengeRun;
use App\Models\DailyLog;
use App\Models\Project;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class DailyChallenge extends Component implements HasForms
{
    use InteractsWithForms;

    public string $challengeDate;

    public ?array $dailyForm = [];

    public $todayEntry;

    public $allProjects;

    public $challengeRunId;

    public array $history = [];

    public array $summary = [];

    public array $projectBreakdown = [];

    public array $aiPanel = [
        'status' => 'empty',
        'summary' => null,
        'tags' => [],
        'coach_tip' => null,
        'share_draft' => null,
        'model' => null,
        'latency_ms' => null,
        'cost_usd' => null,
        'updated_at' => null,
    ];

    public Collection $pendingInvitations;

    public string $inviteCode = '';

    public bool $shouldPollAi = false;

    public bool $canGoPrevious = false;

    public bool $canGoNext = false;

    public int $currentDayNumber = 1;

    public bool $isEditing = false;

    public bool $showReminder = false;

    public ?array $previousEntry = null;

    public function mount(): void
    {
        $this->challengeDate = now()->format('Y-m-d');
        $this->allProjects = collect();
        $this->pendingInvitations = collect();

        $run = $this->ensureChallengeRun();
        $this->form->fill([
            'description' => '',
            'projects_worked_on' => [],
            'hours_coded' => 1,
            'learnings' => null,
            'challenges_faced' => null,
        ]);
        $this->loadTodayEntry($run);
    }

    protected function ensureChallengeRun(): ?ChallengeRun
    {
        $user = auth()->user();

        $run = ChallengeRun::query()
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('participantLinks', fn ($qq) => $qq->where('user_id', $user->id));
            })
            ->latest('start_date')
            ->first();

        $this->refreshPendingInvitations();

        if (! $run) {
            $this->challengeRunId = null;
            $this->allProjects = collect();
            $this->history = [];
            $this->summary = [];
            $this->projectBreakdown = [];

            Notification::make()
                ->title('Aucun challenge actif')
                ->body('Rejoignez ou créez un challenge pour compléter votre journal quotidien.')
                ->warning()
                ->send();

            return null;
        }

        $this->challengeRunId = $run->id;

        $this->refreshProjects($run);
        $this->refreshHistory($run);
        $this->buildSummary($run);

        return $run;
    }

    protected function refreshProjects(ChallengeRun $run): void
    {
        $this->allProjects = Project::query()
            ->where('challenge_run_id', $run->id)
            ->orderBy('name')
            ->get();
    }

    protected function refreshPendingInvitations(): void
    {
        $this->pendingInvitations = ChallengeInvitation::query()
            ->where('email', auth()->user()->email)
            ->whereNull('accepted_at')
            ->with(['run.owner:id,name'])
            ->latest()
            ->get();
    }

    public function goToDay(string $direction): void
    {
        $run = $this->challengeRunId ? ChallengeRun::find($this->challengeRunId) : null;

        if (! $run) {
            return;
        }

        $date = Carbon::parse($this->challengeDate);
        $date = $direction === 'previous' ? $date->subDay() : $date->addDay();

        $this->challengeDate = $this->clampDate($date, $run)->format('Y-m-d');
        $this->loadTodayEntry($run);
    }

    public function setDate(string $date): void
    {
        $run = $this->challengeRunId ? ChallengeRun::find($this->challengeRunId) : null;

        if (! $run) {
            return;
        }

        $this->challengeDate = $this->clampDate(Carbon::parse($date), $run)->format('Y-m-d');
        $this->loadTodayEntry($run);
    }

    public function startEditing(): void
    {
        if (! $this->todayEntry) {
            return;
        }

        $this->isEditing = true;
        $this->form->fill([
            'description' => $this->todayEntry->notes ?? '',
            'projects_worked_on' => collect($this->todayEntry->projects_worked_on ?? [])->map(fn ($id) => (string) $id)->all(),
            'hours_coded' => $this->todayEntry->hours_coded ?? 1,
            'learnings' => $this->todayEntry->learnings,
            'challenges_faced' => $this->todayEntry->challenges_faced,
        ]);
    }

    public function cancelEditing(): void
    {
        $this->isEditing = false;
        $this->loadTodayEntry();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('dailyForm')
            ->columns(2)
            ->components([
                Textarea::make('description')
                    ->label('Description du jour')
                    ->required()
                    ->minLength(10)
                    ->columnSpanFull(),
                CheckboxList::make('projects_worked_on')
                    ->label('Projets travaillés')
                    ->options(fn () => $this->allProjects?->pluck('name', 'id')->mapWithKeys(fn ($label, $id) => [(string) $id => $label])->toArray() ?? [])
                    ->columnSpanFull(),
                TextInput::make('hours_coded')
                    ->label('Heures codées')
                    ->numeric()
                    ->minValue(0.25)
                    ->step(0.25)
                    ->required()
                    ->default(1),
                Textarea::make('learnings')
                    ->label('Apprentissages du jour')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('challenges_faced')
                    ->label('Difficultés rencontrées')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    protected function clampDate(Carbon $date, ChallengeRun $run): Carbon
    {
        $start = Carbon::parse($run->start_date)->startOfDay();
        $end = $start->copy()->addDays(max(0, (int) $run->target_days - 1));
        $max = Carbon::today()->min($end);

        if ($date->lessThan($start)) {
            return $start;
        }

        if ($date->greaterThan($max)) {
            return $max;
        }

        return $date;
    }

    public function loadTodayEntry(?ChallengeRun $run = null): void
    {
        $run ??= ($this->challengeRunId ? ChallengeRun::find($this->challengeRunId) : null);
        if (! $run) {
            return;
        }

        $date = $this->clampDate(Carbon::parse($this->challengeDate), $run);
        $this->challengeDate = $date->format('Y-m-d');

        $start = Carbon::parse($run->start_date)->startOfDay();
        $end = $start->copy()->addDays(max(0, (int) $run->target_days - 1));
        $allowedMax = Carbon::today()->min($end);

        $this->currentDayNumber = max(1, $start->diffInDays($date) + 1);
        $this->canGoPrevious = $date->greaterThan($start);
        $this->canGoNext = $date->lessThan($allowedMax);

        $this->todayEntry = DailyLog::where('challenge_run_id', $run->id)
            ->where('user_id', auth()->id())
            ->where('day_number', $this->currentDayNumber)
            ->first();

        $this->showReminder = ! $this->todayEntry && $date->isToday();

        $this->previousEntry = null;
        if ($this->currentDayNumber > 1) {
            $previous = DailyLog::where('challenge_run_id', $run->id)
                ->where('user_id', auth()->id())
                ->where('day_number', $this->currentDayNumber - 1)
                ->first();

            if ($previous) {
                $this->previousEntry = [
                    'day_number' => $previous->day_number,
                    'date' => $previous->date ? Carbon::parse($previous->date) : null,
                    'hours' => $previous->hours_coded,
                    'notes' => $previous->notes,
                    'projects' => $previous->projects_worked_on ?? [],
                ];
            }
        }

        $this->isEditing = false;

        $entry = $this->todayEntry;

        $this->form->fill([
            'description' => $entry?->notes ?? '',
            'projects_worked_on' => collect($entry?->projects_worked_on ?? [])->map(fn ($id) => (string) $id)->all(),
            'hours_coded' => $entry?->hours_coded ?? 1,
            'learnings' => $entry?->learnings,
            'challenges_faced' => $entry?->challenges_faced,
        ]);

        $this->refreshHistory($run);
        $this->buildSummary($run);
        $this->refreshAiPanel($this->todayEntry);
    }

    public function saveEntry(): void
    {
        $this->form->validate();
        $data = $this->form->getState();

        $run = ChallengeRun::findOrFail($this->challengeRunId);
        $date = Carbon::parse($this->challengeDate);
        $dayNumber = Carbon::parse($run->start_date)->diffInDays($date) + 1;

        $log = DailyLog::updateOrCreate(
            [
                'challenge_run_id' => $run->id,
                'user_id' => auth()->id(),
                'day_number' => $dayNumber,
            ],
            [
                'date' => $date->toDateString(),
                'hours_coded' => isset($data['hours_coded']) ? (float) $data['hours_coded'] : 1,
                'projects_worked_on' => collect($data['projects_worked_on'] ?? [])->map(fn ($id) => (string) $id)->all(),
                'notes' => $data['description'] ?? '',
                'learnings' => $data['learnings'] ?? null,
                'challenges_faced' => $data['challenges_faced'] ?? null,
                'completed' => true,
            ]
        );

        $log->queueAiGeneration();

        $hours = isset($data['hours_coded']) ? (float) $data['hours_coded'] : 0.0;
        $yesterday = DailyLog::where('challenge_run_id', $run->id)
            ->where('user_id', auth()->id())
            ->where('day_number', max(1, $dayNumber - 1))
            ->first();

        $delta = $yesterday ? $hours - (float) $yesterday->hours_coded : null;
        $deltaText = $delta === null ? '' : ($delta >= 0 ? '+' : '−').number_format(abs($delta), 2).' h vs veille';

        Notification::make()
            ->title('Journal sauvegardé')
            ->body(trim('Vous avez enregistré '.number_format($hours, 2).' h aujourd’hui. '.$deltaText))
            ->success()
            ->send();

        session()->flash('message', 'Entrée quotidienne sauvegardée !');
        $this->isEditing = false;
        $this->loadTodayEntry($run);
    }

    public function regenerateAi(): void
    {
        if (! $this->todayEntry) {
            Notification::make()
                ->title('Aucun journal à régénérer')
                ->body("Créez d'abord votre entrée du jour avant de relancer l'IA.")
                ->warning()
                ->send();

            return;
        }

        $log = $this->todayEntry->fresh();

        if (! $log) {
            Notification::make()
                ->title('Entrée introuvable')
                ->body('Veuillez recharger la page et réessayer.')
                ->danger()
                ->send();

            return;
        }

        if (GenerateDailyLogInsights::isThrottledFor($log)) {
            Notification::make()
                ->title('Régénération IA limitée')
                ->body("Une génération IA a déjà été effectuée aujourd'hui. Réessayez demain.")
                ->info()
                ->send();

            return;
        }

        $log->queueAiGeneration();

        Notification::make()
            ->title('Régénération planifiée')
            ->body("Le résumé IA sera mis à jour dans les prochaines minutes.")
            ->send();

        $this->refreshAiPanel($log);
        $this->aiPanel['status'] = 'pending';
        $this->shouldPollAi = true;
    }

    protected function refreshAiPanel(?DailyLog $log): void
    {
        if (! $log) {
            $this->aiPanel = [
                'status' => 'empty',
                'summary' => null,
                'tags' => [],
                'coach_tip' => null,
                'share_draft' => null,
                'model' => null,
                'latency_ms' => null,
                'cost_usd' => null,
                'updated_at' => null,
            ];

            $this->shouldPollAi = false;

            return;
        }

        $status = filled($log->summary_md) ? 'ready' : 'pending';

        $this->aiPanel = [
            'status' => $status,
            'summary' => $log->summary_md,
            'tags' => $log->tags ?? [],
            'coach_tip' => $log->coach_tip,
            'share_draft' => $log->share_draft,
            'model' => $log->ai_model,
            'latency_ms' => $log->ai_latency_ms,
            'cost_usd' => $log->ai_cost_usd,
            'updated_at' => $log->updated_at,
        ];

        $this->shouldPollAi = $status === 'pending';
    }

    protected function refreshHistory(ChallengeRun $run): void
    {
        $history = DailyLog::query()
            ->where('challenge_run_id', $run->id)
            ->where('user_id', auth()->id())
            ->orderByDesc('date')
            ->orderByDesc('day_number')
            ->limit(10)
            ->get();

        $this->history = $history->map(function (DailyLog $log) {
            return [
                'day_number' => $log->day_number,
                'date' => $log->date ? Carbon::parse($log->date)->format('Y-m-d') : null,
                'hours' => $log->hours_coded,
                'projects' => $log->projects_worked_on ?? [],
                'notes' => $log->notes,
            ];
        })->toArray();
    }

    protected function buildSummary(ChallengeRun $run): void
    {
        $logs = DailyLog::query()
            ->where('challenge_run_id', $run->id)
            ->where('user_id', auth()->id())
            ->orderByDesc('date')
            ->get();

        $totalLogs = $logs->count();
        $totalHours = (float) $logs->sum('hours_coded');
        $target = max(1, (int) $run->target_days);
        $lastLog = $logs->sortByDesc(fn ($log) => $log->date ?? $log->created_at)->first();

        $weekStart = Carbon::today()->startOfWeek();
        $hoursThisWeek = $logs->filter(function (DailyLog $log) use ($weekStart) {
            if (! $log->date) {
                return false;
            }

            return Carbon::parse($log->date)->greaterThanOrEqualTo($weekStart);
        })->sum('hours_coded');

        $this->summary = [
            'streak' => $this->computeStreak($logs),
            'totalLogs' => $totalLogs,
            'totalHours' => round($totalHours, 2),
            'completion' => (int) round(min(100, ($totalLogs / $target) * 100)),
            'averageHours' => $totalLogs > 0 ? round($totalHours / $totalLogs, 2) : 0.0,
            'hoursThisWeek' => round((float) $hoursThisWeek, 2),
            'lastLogAt' => $lastLog?->date ? Carbon::parse($lastLog->date) : null,
        ];

        $projectCounts = [];
        foreach ($logs as $log) {
            foreach ($log->projects_worked_on ?? [] as $projectId) {
                $projectCounts[$projectId] = ($projectCounts[$projectId] ?? 0) + 1;
            }
        }

        $projects = $this->allProjects instanceof Collection ? $this->allProjects : collect($this->allProjects);

        $this->projectBreakdown = collect($projectCounts)
            ->map(function (int $count, string $projectId) use ($projects) {
                $project = $projects->firstWhere('id', $projectId);

                return [
                    'id' => $projectId,
                    'name' => $project?->name ?? 'Projet supprimé',
                    'count' => $count,
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(5)
            ->all();
    }

    protected function computeStreak(Collection $logs): int
    {
        $dates = $logs
            ->filter(fn (DailyLog $log) => $log->date)
            ->map(fn (DailyLog $log) => Carbon::parse($log->date)->startOfDay())
            ->unique()
            ->sortDesc()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $today = Carbon::today();
        $expected = $today->copy();
        $streak = 0;

        foreach ($dates as $date) {
            if ($streak === 0) {
                if ($date->isSameDay($expected) || $date->isSameDay($expected->copy()->subDay())) {
                    $streak++;
                    $expected = $date->copy()->subDay();
                } else {
                    break;
                }

                continue;
            }

            if ($date->isSameDay($expected)) {
                $streak++;
                $expected->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    public function render(): View
    {
        $run = $this->challengeRunId ? ChallengeRun::find($this->challengeRunId) : null;

        return view('livewire.page.daily-challenge', [
            'run' => $run,
        ]);
    }

    public function pollAiPanel(): void
    {
        if (! $this->shouldPollAi || ! $this->todayEntry) {
            return;
        }

        $log = DailyLog::with('challengeRun')->find($this->todayEntry->id);

        if (! $log) {
            return;
        }

        $this->todayEntry = $log;
        $this->refreshAiPanel($log);
    }

    public function acceptInvitation(string $invitationId): void
    {
        $invitation = ChallengeInvitation::query()
            ->whereKey($invitationId)
            ->whereNull('accepted_at')
            ->where('email', auth()->user()->email)
            ->with('run')
            ->first();

        if (! $invitation) {
            Notification::make()
                ->title('Invitation introuvable')
                ->body('Cette invitation a déjà été utilisée ou a expiré.')
                ->warning()
                ->send();

            $this->refreshPendingInvitations();

            return;
        }

        $run = $this->finalizeInvitation($invitation);
        $this->challengeRunId = $run->id;
        $this->loadTodayEntry($run);

        Notification::make()
            ->title('Invitation acceptée')
            ->body("Vous avez rejoint le challenge « {$run->title} ». Bon courage !")
            ->success()
            ->send();
    }

    public function joinWithCode(): void
    {
        $code = Str::upper(trim($this->inviteCode));

        if ($code === '') {
            Notification::make()
                ->title('Code requis')
                ->body('Saisissez un code d’invitation ou de challenge public.')
                ->warning()
                ->send();

            return;
        }

        $invitation = ChallengeInvitation::query()
            ->where('token', $code)
            ->whereNull('accepted_at')
            ->with('run')
            ->first();

        if ($invitation) {
            if ($invitation->email !== auth()->user()->email) {
                Notification::make()
                    ->title('Invitation réservée')
                    ->body('Ce code est associé à une autre adresse email.')
                    ->danger()
                    ->send();

                return;
            }

            $run = $this->finalizeInvitation($invitation);
            $this->inviteCode = '';
            $this->challengeRunId = $run->id;
            $this->loadTodayEntry($run);
            $this->refreshPendingInvitations();

            Notification::make()
                ->title('Invitation acceptée')
                ->body("Vous avez rejoint le challenge « {$run->title} ». Bon courage !")
                ->success()
                ->send();

            return;
        }

        $run = ChallengeRun::query()
            ->where('public_join_code', $code)
            ->where('is_public', true)
            ->first();

        if (! $run) {
            Notification::make()
                ->title('Code invalide')
                ->body('Impossible de trouver un challenge pour ce code.')
                ->danger()
                ->send();

            return;
        }

        $this->attachToRun($run);
        $this->inviteCode = '';
        $this->challengeRunId = $run->id;
        $this->loadTodayEntry($run);
        $this->refreshPendingInvitations();

        Notification::make()
            ->title('Challenge rejoint')
            ->body("Vous avez rejoint « {$run->title} ». Bon challenge !")
            ->success()
            ->send();
    }

    protected function finalizeInvitation(ChallengeInvitation $invitation): ChallengeRun
    {
        $run = $invitation->run;

        $this->attachToRun($run);

        if (! $invitation->accepted_at) {
            $invitation->forceFill(['accepted_at' => now()])->save();
        }

        $this->refreshPendingInvitations();

        return $run;
    }

    protected function attachToRun(ChallengeRun $run): void
    {
        $run->participantLinks()->firstOrCreate(
            ['user_id' => auth()->id()],
            ['joined_at' => now()]
        );

        $this->refreshProjects($run);
    }
}
