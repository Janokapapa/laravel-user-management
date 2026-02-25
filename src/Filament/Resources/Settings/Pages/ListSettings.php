<?php

namespace JanDev\UserManagement\Filament\Resources\Settings\Pages;

use JanDev\UserManagement\Filament\Resources\SettingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
