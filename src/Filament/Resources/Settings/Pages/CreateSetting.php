<?php

namespace JanDev\UserManagement\Filament\Resources\Settings\Pages;

use JanDev\UserManagement\Filament\Resources\SettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The value textarea contains a JSON string — decode it before saving
        // The model's JSON cast will re-encode it
        if (isset($data['value']) && is_string($data['value'])) {
            $decoded = json_decode($data['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['value'] = $decoded;
            }
        }
        return $data;
    }
}
