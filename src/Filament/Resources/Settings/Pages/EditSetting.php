<?php

namespace JanDev\UserManagement\Filament\Resources\Settings\Pages;

use JanDev\UserManagement\Filament\Resources\SettingResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // The value textarea contains a JSON string — decode it before saving
        if (isset($data['value']) && is_string($data['value'])) {
            $decoded = json_decode($data['value'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['value'] = $decoded;
            }
        }
        return $data;
    }
}
