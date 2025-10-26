<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return __('Modifier :name', ['name' => $this->getRecord()->name]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('Utilisateur mis à jour');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('is_admin', $data)) {
            $data['is_admin'] = (bool) $data['is_admin'];
        }

        return $data;
    }
}
