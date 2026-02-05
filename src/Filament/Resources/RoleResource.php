<?php

namespace JanDev\UserManagement\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use JanDev\UserManagement\Filament\Resources\Roles\Pages\CreateRole;
use JanDev\UserManagement\Filament\Resources\Roles\Pages\EditRole;
use JanDev\UserManagement\Filament\Resources\Roles\Pages\ListRoles;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super-admin') || auth()->user()?->hasPermissionTo('manage roles');
    }

    public static function getNavigationLabel(): string
    {
        return __('Roles');
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('user-management.navigation_group', 'User Management'));
    }

    public static function getNavigationSort(): ?int
    {
        return config('user-management.navigation_sort', 100) + 1;
    }

    public static function getModelLabel(): string
    {
        return __('Role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Roles');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Role Information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Select::make('permissions')
                        ->label(__('Permissions'))
                        ->multiple()
                        ->relationship('permissions', 'name')
                        ->preload()
                        ->searchable(),
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

                TextColumn::make('permissions.name')
                    ->label(__('Permissions'))
                    ->badge()
                    ->separator(', ')
                    ->limitList(3)
                    ->expandableLimitedList(),

                TextColumn::make('users_count')
                    ->label(__('Users'))
                    ->counts('users')
                    ->sortable(),

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
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
