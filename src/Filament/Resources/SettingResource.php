<?php

namespace JanDev\UserManagement\Filament\Resources;

use JanDev\UserManagement\Filament\Resources\Settings\Pages;
use JanDev\UserManagement\Models\Setting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('user-management.system_navigation_group', 'System');
    }

    public static function getModelLabel(): string
    {
        return __('Setting');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Settings');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('group')
                    ->label(__('Group'))
                    ->required()
                    ->maxLength(255)
                    ->helperText(__('Category for the setting (e.g. audience, general)')),

                TextInput::make('key')
                    ->label(__('Key'))
                    ->required()
                    ->maxLength(255)
                    ->helperText(__('Unique key within the group')),

                Textarea::make('value')
                    ->label(__('Value (JSON)'))
                    ->required()
                    ->rows(8)
                    ->helperText(__('JSON value. Must be valid JSON (string, number, array or object). Examples: "text", 42, true, [], {}'))
                    ->rules([
                        function () {
                            return function (string $attribute, mixed $value, \Closure $fail) {
                                if ($value === null || $value === '') {
                                    return;
                                }
                                json_decode($value);
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                    $fail(__('The value must be valid JSON.'));
                                }

                                // Validate slugs and count if this is custom_fields definition
                                $decoded = json_decode($value, true);
                                if (is_array($decoded)) {
                                    if (count($decoded) > 20) {
                                        $fail(__('Maximum 20 custom fields allowed.'));
                                        return;
                                    }
                                    foreach ($decoded as $item) {
                                        if (isset($item['slug'])) {
                                            if (!preg_match('/^[a-zA-Z0-9_]+$/', $item['slug'])) {
                                                $fail(__('Custom field slugs must only contain letters, numbers and underscores.'));
                                                return;
                                            }
                                        }
                                    }
                                }
                            };
                        },
                    ])
                    ->dehydrateStateUsing(fn ($state) => $state)
                    ->afterStateHydrated(function ($component, $state) {
                        if (is_array($state) || is_object($state)) {
                            $component->state(json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label(__('Group'))
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->label(__('Key'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('value')
                    ->label(__('Value'))
                    ->limit(60)
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state) : $state),

                TextColumn::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label(__('Group'))
                    ->options(fn () => Setting::query()->distinct()->pluck('group', 'group')->toArray())
                    ->placeholder(__('All Groups')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit'   => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
