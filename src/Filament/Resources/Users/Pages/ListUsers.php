<?php

namespace JanDev\UserManagement\Filament\Resources\Users\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use JanDev\UserManagement\Filament\Resources\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
