<?php

namespace JanDev\UserManagement\Filament\Resources\Permissions\Pages;

use Filament\Resources\Pages\CreateRecord;
use JanDev\UserManagement\Filament\Resources\PermissionResource;

class CreatePermission extends CreateRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
