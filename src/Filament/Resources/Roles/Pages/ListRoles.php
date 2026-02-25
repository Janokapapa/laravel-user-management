<?php

namespace JanDev\UserManagement\Filament\Resources\Roles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use JanDev\UserManagement\Filament\Resources\RoleResource;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
