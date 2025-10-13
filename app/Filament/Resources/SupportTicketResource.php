<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportTicketResource\Pages;
use App\Jobs\CreateSupportTicketGitHubIssue;
use App\Models\SupportTicket;
use App\Services\GitHub\GitHubApiException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;
use Throwable;

class SupportTicketResource extends Resource
{
    protected static ?string $model = SupportTicket::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLifebuoy;

    protected static string|\UnitEnum|null $navigationGroup = 'Support';

    protected static ?int $navigationSort = 0;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Reçu le'))
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label(__('Nom'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->label(__('Catégorie'))
                    ->badge()
                    ->colors([
                        'question' => 'gray',
                        'idea' => 'info',
                        'bug' => 'danger',
                    ])
                    ->formatStateUsing(fn(?string $state) => $state ? ucfirst($state) : '—'),
                TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge()
                    ->colors([
                        'open' => 'warning',
                        'in_progress' => 'info',
                        'resolved' => 'success',
                    ])
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'resolved' => 'Résolu',
                        'in_progress' => 'En cours',
                        default => 'Ouvert',
                    }),
                IconColumn::make('github_issue_url')
                    ->label('Issue GitHub')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-top-right-on-square')
                    ->falseIcon('heroicon-o-minus')
                    ->url(fn(SupportTicket $record) => $record->github_issue_url)
                    ->openUrlInNewTab(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'open' => 'Ouvert',
                        'in_progress' => 'En cours',
                        'resolved' => 'Résolu',
                    ]),
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'question' => 'Question',
                        'idea' => 'Idée',
                        'bug' => 'Bug',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('Voir')
                        ->icon('heroicon-o-eye')
                        ->modalHeading(fn(SupportTicket $record) => 'Ticket ' . $record->id)
                        ->modalWidth('lg')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nom')
                                ->disabled(),
                            TextInput::make('email')
                                ->label('Email')
                                ->disabled(),
                            TextInput::make('category')
                                ->label('Catégorie')
                                ->disabled(),
                            TextInput::make('status')
                                ->label('Statut')
                                ->disabled(),
                            Textarea::make('message')
                                ->label('Message')
                                ->rows(8)
                                ->disabled()
                                ->columnSpanFull(),
                            TextInput::make('github_issue_url')
                                ->label('Issue GitHub')
                                ->disabled(),
                        ])
                        ->fillForm(fn(SupportTicket $record) => [
                            'name' => $record->name,
                            'email' => $record->email,
                            'category' => ucfirst((string) $record->category),
                            'status' => match ($record->status) {
                                'resolved' => 'Résolu',
                                'in_progress' => 'En cours',
                                default => 'Ouvert',
                            },
                            'message' => $record->message,
                            'github_issue_url' => $record->github_issue_url,
                        ])
                        ->action(fn() => null),
                    Action::make('create_issue')
                        ->label('Créer issue GitHub')
                        ->icon('heroicon-o-plus-circle')
                        ->color('primary')
                        ->visible(fn(SupportTicket $record) => blank($record->github_issue_url))
                        ->requiresConfirmation()
                        ->modalHeading('Créer une issue GitHub')
                        ->modalDescription('Une issue sera créée dans le dépôt support configuré.')
                        ->action(function (SupportTicket $record): void {
                            try {
                                CreateSupportTicketGitHubIssue::dispatchSync($record);

                                $record->refresh();

                                Notification::make()
                                    ->title('Issue GitHub créée')
                                    ->success()
                                    ->send();
                            } catch (RuntimeException|GitHubApiException $exception) {
                                report($exception);

                                Notification::make()
                                    ->title('Création impossible')
                                    ->body($exception->getMessage())
                                    ->danger()
                                    ->send();
                            } catch (Throwable $exception) {
                                report($exception);

                                Notification::make()
                                    ->title('Création impossible')
                                    ->body('Une erreur inattendue est survenue lors de la création de l’issue.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                    Action::make('mark_in_progress')
                        ->label('En cours')
                        ->icon('heroicon-o-arrow-path-rounded-square')
                        ->color('warning')
                        ->visible(fn(SupportTicket $record) => $record->status === 'open')
                        ->action(fn(SupportTicket $record) => $record->update([
                            'status' => 'in_progress',
                        ])),
                    Action::make('resolve')
                        ->label('Résoudre')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn(SupportTicket $record) => $record->status !== 'resolved')
                        ->action(fn(SupportTicket $record) => $record->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ])),
                    Action::make('reopen')
                        ->label('Rouvrir')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('primary')
                        ->visible(fn(SupportTicket $record) => $record->status === 'resolved')
                        ->action(fn(SupportTicket $record) => $record->update([
                            'status' => 'open',
                            'resolved_at' => null,
                        ])),
                    Action::make('link_issue')
                        ->label('Lier issue')
                        ->icon('heroicon-o-link')
                        ->color('info')
                        ->form([
                            TextInput::make('github_issue_url')
                                ->label('URL de l’issue')
                                ->url()
                                ->required()
                                ->maxLength(255),
                        ])
                        ->fillForm(fn(SupportTicket $record) => [
                            'github_issue_url' => $record->github_issue_url,
                        ])
                        ->visible(fn(SupportTicket $record) => $record->status !== 'resolved')
                        ->action(function (SupportTicket $record, array $data): void {
                            $record->update([
                                'github_issue_url' => $data['github_issue_url'] ?? null,
                                'status' => 'in_progress',
                            ]);
                        }),
                ])
            ])
            ->toolbarActions([
                BulkAction::make('resolveSelected')
                    ->label('Résoudre')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each(fn(SupportTicket $record) => $record->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ]));
                    }),
                BulkAction::make('reopenSelected')
                    ->label('Rouvrir')
                    ->color('primary')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each(fn(SupportTicket $record) => $record->update([
                            'status' => 'open',
                            'resolved_at' => null,
                        ]));
                    }),
            ])
            ->emptyStateHeading('Aucun ticket pour le moment')
            ->emptyStateDescription('Les retours utilisateurs apparaîtront ici pour être traités.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupportTickets::route('/'),
        ];
    }
}
