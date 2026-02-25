<?php

namespace JanDev\UserManagement\Filament\Resources\Users\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use JanDev\UserManagement\Filament\Resources\UserResource;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
