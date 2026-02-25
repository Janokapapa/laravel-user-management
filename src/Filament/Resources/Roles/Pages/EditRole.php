<?php

namespace JanDev\UserManagement\Filament\Resources\Roles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use JanDev\UserManagement\Filament\Resources\RoleResource;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

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
