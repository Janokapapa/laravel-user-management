<?php

namespace JanDev\UserManagement\Filament\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use JanDev\UserManagement\Filament\Resources\RoleResource;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
