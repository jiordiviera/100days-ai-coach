<?php

namespace App\Livewire\Partials;

use App\Models\UserRepository;
use App\Services\GitHub\GitHubApiException;
use App\Services\GitHub\GitHubTemplateService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class GithubTemplateSetup extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $githubForm = null;

    public array $owners = [];

    public ?UserRepository $repository = null;

    public ?string $errorMessage = null;

    public bool $isReady = false;

    public bool $isProcessing = false;

    public function mount(GitHubTemplateService $templateService): void
    {
        $user = auth()->user();
        $this->repository = $user->repositories()->where('provider', 'github')->first();

        if (! $user->profile?->github_access_token) {
            $this->errorMessage = 'Connectez votre compte GitHub pour créer automatiquement votre repository.';

            return;
        }

        try {
            $owners = $templateService->listInstallableOwners($user);
            $this->owners = $owners;
        } catch (GitHubApiException $exception) {
            $this->errorMessage = $exception->getMessage();
        }

        $defaultOwner = $this->repository?->repo_owner
            ?: (Arr::first($this->owners)['login'] ?? $user->profile?->github_username);

        $defaultVisibility = $this->repository?->visibility
            ?: (config('services.github.template.visibility', 'private'));

        $defaultName = $this->repository?->repo_name
            ?: '100days-of-code-'.Str::slug($user->name ?: 'journey');

        $this->githubForm = [
            'owner' => $defaultOwner,
            'repo_name' => $defaultName,
            'visibility' => $defaultVisibility,
        ];

        $this->form->fill($this->githubForm);

        $this->isReady = true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('githubForm')
            ->components([
                TextInput::make('repo_name')
                    ->label('Nom du repository')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Utilise uniquement des lettres, chiffres et tirets.'),
                Select::make('owner')
                    ->label('Destination')
                    ->options(collect($this->owners)
                        ->mapWithKeys(fn ($owner) => [$owner['login'] => $owner['display'] ?? $owner['login']])
                        ->toArray())
                    ->searchable()
                    ->required(),
                ToggleButtons::make('visibility')
                    ->label('Visibilité')
                    ->options([
                        'public' => 'Public',
                        'private' => 'Privé',
                    ])
                    ->inline()
                    ->default('private')
                    ->required(),
            ]);
    }

    public function createRepository(GitHubTemplateService $templateService): void
    {
        $this->form->validate();
        $this->isProcessing = true;
        $this->errorMessage = null;

        $data = $this->form->getState();

        try {
            $user = auth()->user()->fresh(['profile', 'repositories']);
            $repo = $templateService->provision(
                $user,
                $data['repo_name'] ?? '',
                $data['visibility'] ?? 'private',
                $data['owner'] ?? null,
            );

            $this->repository = $repo;
            $this->githubForm = [
                'owner' => $repo->repo_owner,
                'repo_name' => $repo->repo_name,
                'visibility' => $repo->visibility,
            ];
            Notification::make()
                ->title('Repository créé')
                ->body('Ton template GitHub est prêt. Tu peux commencer à documenter ta progression !')
                ->success()
                ->send();
        } catch (GitHubApiException $exception) {
            $this->errorMessage = $exception->getMessage();
            Notification::make()
                ->title('Création impossible')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isProcessing = false;
        }
    }

    public function render(): View
    {
        return view('livewire.partials.github-template-setup');
    }
}
