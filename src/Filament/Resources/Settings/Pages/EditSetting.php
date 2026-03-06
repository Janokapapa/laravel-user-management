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
        // system.email_send_config: assemble value from individual send_* fields
        if (($data['group'] ?? '') === 'system' && ($data['key'] ?? '') === 'email_send_config') {
            $data['value'] = [
                'max_per_run'            => (int) ($data['send_max_per_run'] ?? 100),
                'delay_seconds'          => (int) ($data['send_delay_seconds'] ?? 1),
                'mailgun_batch_size'     => (int) ($data['send_mailgun_batch_size'] ?? 500),
                'mailgun_batch_delay_ms' => (int) ($data['send_mailgun_batch_delay_ms'] ?? 2000),
            ];
            unset(
                $data['send_max_per_run'],
                $data['send_delay_seconds'],
                $data['send_mailgun_batch_size'],
                $data['send_mailgun_batch_delay_ms'],
            );
            return $data;
        }

        // parkfly.config: assemble value from individual parkfly_* fields
        if (($data['group'] ?? '') === 'parkfly' && ($data['key'] ?? '') === 'config') {
            $data['value'] = [
                'maxhely'         => (int) ($data['parkfly_maxhely'] ?? 0),
                'folia_ar'        => (int) ($data['parkfly_folia_ar'] ?? 0),
                'kulso_mosas'     => (int) ($data['parkfly_kulso_mosas'] ?? 0),
                'belso_mosas'     => (int) ($data['parkfly_belso_mosas'] ?? 0),
                'van_mosas'       => (int) ($data['parkfly_van_mosas'] ?? 0),
                'minimum_voucher' => (int) ($data['parkfly_minimum_voucher'] ?? 0),
            ];
            // Remove temporary fields
            unset(
                $data['parkfly_maxhely'],
                $data['parkfly_folia_ar'],
                $data['parkfly_kulso_mosas'],
                $data['parkfly_belso_mosas'],
                $data['parkfly_van_mosas'],
                $data['parkfly_minimum_voucher'],
            );
            return $data;
        }

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
