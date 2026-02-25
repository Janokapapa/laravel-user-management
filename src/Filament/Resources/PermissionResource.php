<?php

namespace JanDev\UserManagement\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use JanDev\UserManagement\Filament\Resources\Permissions\Pages\CreatePermission;
use JanDev\UserManagement\Filament\Resources\Permissions\Pages\EditPermission;
use JanDev\UserManagement\Filament\Resources\Permissions\Pages\ListPermissions;
use Spatie\Permission\Models\Permission;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super-admin') || auth()->user()?->hasPermissionTo('manage permissions');
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canCreate(): bool
    {
        return static::canAccess();
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canAccess();
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return static::canAccess();
    }

    public static function getNavigationLabel(): string
    {
        return __('Permissions');
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('user-management.navigation_group', 'User Management'));
    }

    public static function getNavigationSort(): ?int
    {
        return config('user-management.navigation_sort', 100) + 2;
    }

    public static function getModelLabel(): string
    {
        return __('Permission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Permissions');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Permission Information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText(__('Use lowercase with spaces, e.g. "manage users"')),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->badge()
                    ->separator(', '),

                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
