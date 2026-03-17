<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->id === auth()->id()) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('No puedes eliminarte a ti mismo')
                            ->body('No puedes eliminar tu propio usuario.')
                            ->send();
                            
                        return false;
                    }
                }),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Usuario actualizado';
    }

    protected function getSavedNotificationBody(): ?string
    {
        return 'Los datos del usuario han sido actualizados correctamente.';
    }
}
