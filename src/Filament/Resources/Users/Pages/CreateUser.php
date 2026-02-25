<?php

namespace JanDev\UserManagement\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use JanDev\UserManagement\Filament\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
