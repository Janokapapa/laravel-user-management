<?php

namespace JanDev\UserManagement\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use JanDev\UserManagement\Filament\Resources\Users\Pages\CreateUser;
use JanDev\UserManagement\Filament\Resources\Users\Pages\EditUser;
use JanDev\UserManagement\Filament\Resources\Users\Pages\ListUsers;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super-admin') || auth()->user()?->hasPermissionTo('manage users');
    }

    public static function getModel(): string
    {
        return config('user-management.user_model', \App\Models\User::class);
    }

    public static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getNavigationGroup(): ?string
    {
        return __(config('user-management.navigation_group', 'User Management'));
    }

    public static function getNavigationSort(): ?int
    {
        return config('user-management.navigation_sort', 100);
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('User Information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label(__('Email'))
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label(__('Password'))
                        ->password()
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                        ->required(fn($livewire) => $livewire instanceof CreateRecord)
                        ->dehydrated(fn($state) => filled($state))
                        ->autocomplete('new-password'),

                    DateTimePicker::make('email_verified_at')
                        ->label(__('Email verified at'))
                        ->native(false)
                        ->nullable(),
                ])
                ->columns(2),

            Section::make(__('Roles & Permissions'))
                ->schema([
                    Select::make('roles')
                        ->label(__('Roles'))
                        ->multiple()
                        ->relationship('roles', 'name')
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

                TextColumn::make('email')
                    ->label(__('Email'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label(__('Roles'))
                    ->badge()
                    ->separator(', '),

                TextColumn::make('email_verified_at')
                    ->label(__('Verified'))
                    ->dateTime('Y-m-d H:i')
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
