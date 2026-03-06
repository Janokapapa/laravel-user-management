<?php

namespace JanDev\UserManagement\Filament\Resources;

use JanDev\UserManagement\Filament\Resources\Settings\Pages;
use JanDev\UserManagement\Models\Setting;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
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

    /**
     * Check if a record's group+key is handled by a custom UI (not the generic Textarea).
     */
    protected static function isRepeaterRecord(string $group, string $key): bool
    {
        return ($group === 'audience' && $key === 'custom_fields')
            || ($group === 'email' && $key === 'senders')
            || ($group === 'email' && $key === 'pmta_servers')
            || ($group === 'email' && $key === 'smtp_servers')
            || ($group === 'email' && $key === 'domain_routing')
            || ($group === 'email' && $key === 'routing_profiles')
            || ($group === 'email' && $key === 'send_config')
            || ($group === 'parkfly' && $key === 'config');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('group')
                    ->label(__('Group'))
                    ->required()
                    ->maxLength(255)
                    ->live()
                    ->helperText(__('Category for the setting (e.g. audience, general)')),

                TextInput::make('key')
                    ->label(__('Key'))
                    ->required()
                    ->maxLength(255)
                    ->live()
                    ->helperText(__('Unique key within the group')),

                // Repeater UI for audience.custom_fields
                Repeater::make('value')
                    ->label(__('Custom Field Definitions'))
                    ->visible(fn (Get $get): bool => $get('group') === 'audience' && $get('key') === 'custom_fields')
                    ->dehydrated(fn (Get $get): bool => $get('group') === 'audience' && $get('key') === 'custom_fields')
                    ->schema([
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->rules(['regex:/^[a-zA-Z0-9_]+$/'])
                            ->helperText(__('Only letters, numbers and underscores'))
                            ->maxLength(50),

                        TextInput::make('name')
                            ->label(__('Display Name'))
                            ->required()
                            ->maxLength(100),

                        Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                'text' => __('Text'),
                                'number' => __('Number'),
                                'boolean' => __('Boolean'),
                                'date' => __('Date'),
                                'select' => __('Select (Dropdown)'),
                            ])
                            ->required()
                            ->live(),

                        TagsInput::make('options')
                            ->label(__('Options'))
                            ->helperText(__('Press Enter after each option'))
                            ->visible(fn (Get $get): bool => $get('type') === 'select'),

                        Toggle::make('required')
                            ->label(__('Required'))
                            ->default(false),

                        TextInput::make('sort_order')
                            ->label(__('Sort Order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(3)
                    ->maxItems(20)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['name'] ?? '') . ' (' . ($state['slug'] ?? '') . ')')
                    ->defaultItems(0),

                // Repeater UI for email.senders
                Repeater::make('value')
                    ->label(__('Email Senders'))
                    ->visible(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'senders')
                    ->dehydrated(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'senders')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Sender Name (unique ID)'))
                            ->required()
                            ->maxLength(100)
                            ->rules([
                                'regex:/^[a-zA-Z0-9_-]+$/',
                                function () {
                                    return function (string $attribute, mixed $value, \Closure $fail) {
                                        // Uniqueness is enforced at save time via form validation
                                    };
                                },
                            ])
                            ->helperText(__('Unique identifier (letters, numbers, dashes, underscores)')),

                        Select::make('type')
                            ->label(__('Type'))
                            ->options([
                                'smtp' => __('SMTP'),
                                'pmta' => __('PMTA'),
                                'mailgun' => __('Mailgun'),
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('from_address')
                            ->label(__('From Address'))
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('from_name')
                            ->label(__('From Name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('reply_to')
                            ->label(__('Reply-To'))
                            ->email()
                            ->maxLength(255),

                        Toggle::make('enabled')
                            ->label(__('Enabled'))
                            ->default(true),

                        Toggle::make('is_default')
                            ->label(__('Default Sender'))
                            ->default(false),

                        // SMTP-specific fields
                        Select::make('smtp_server')
                            ->label(__('SMTP Server'))
                            ->options(fn () => method_exists(\JanDev\EmailSystem\Support\SenderResolver::class, 'smtpServers')
                                ? collect(\JanDev\EmailSystem\Support\SenderResolver::smtpServers())
                                    ->mapWithKeys(fn ($s) => [$s['name'] => $s['name'] . ' (' . ($s['host'] ?? '') . ')'])
                                    ->toArray()
                                : [])
                            ->visible(fn (Get $get): bool => $get('type') === 'smtp')
                            ->helperText(__('Server defined in SMTP Servers setting')),

                        TextInput::make('smtp_mailer')
                            ->label(__('SMTP Mailer (config/mail.php mailer name, fallback)'))
                            ->default('smtp')
                            ->maxLength(100)
                            ->visible(fn (Get $get): bool => $get('type') === 'smtp' && empty(\JanDev\EmailSystem\Support\SenderResolver::smtpServers()))
                            ->helperText(__('Fallback: mailer name from config/mail.php (used if no SMTP Server selected)')),

                        // PMTA-specific fields
                        Select::make('pmta_server')
                            ->label(__('PMTA Server'))
                            ->options(fn () => collect(\JanDev\EmailSystem\Support\SenderResolver::pmtaServers())
                                ->mapWithKeys(fn ($s) => [$s['name'] => $s['name'] . ' (' . ($s['host'] ?? '') . ')'])
                                ->toArray())
                            ->visible(fn (Get $get): bool => $get('type') === 'pmta')
                            ->helperText(__('Server defined in PMTA Servers setting')),

                        TextInput::make('pmta_virtual_mta')
                            ->label(__('Virtual MTA Override'))
                            ->maxLength(100)
                            ->visible(fn (Get $get): bool => $get('type') === 'pmta')
                            ->helperText(__('Overrides server\'s Virtual MTA if set')),

                        Select::make('routing_profile')
                            ->label(__('Routing Profile'))
                            ->options(fn () => \JanDev\EmailSystem\Support\SenderResolver::routingProfileOptions())
                            ->visible(fn (Get $get): bool => $get('type') === 'pmta')
                            ->helperText(__('Domain routing profile. If empty, all mail goes to PMTA Server above.')),

                        // Mailgun-specific fields
                        TextInput::make('mailgun_domain')
                            ->label(__('Mailgun Domain'))
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('type') === 'mailgun'),

                        TextInput::make('mailgun_secret')
                            ->label(__('Mailgun API Key'))
                            ->password()
                            ->revealable()
                            ->maxLength(500)
                            ->visible(fn (Get $get): bool => $get('type') === 'mailgun'),

                        TextInput::make('mailgun_endpoint')
                            ->label(__('Mailgun Endpoint'))
                            ->default('https://api.mailgun.net')
                            ->maxLength(500)
                            ->visible(fn (Get $get): bool => $get('type') === 'mailgun')
                            ->helperText(__('e.g. https://api.eu.mailgun.net for EU')),

                        // Tracking options
                        Toggle::make('track_clicks')
                            ->label(__('Click Tracking'))
                            ->default(true)
                            ->helperText(__('Rewrite links to track clicks')),

                        Toggle::make('track_opens')
                            ->label(__('Open Tracking'))
                            ->default(false)
                            ->helperText(__('Embed tracking pixel for open detection')),
                    ])
                    ->columns(2)
                    ->maxItems(20)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => (empty($state['enabled'] ?? true) ? '[DISABLED] ' : '') . ($state['name'] ?? __('New Sender')) . ' (' . ($state['type'] ?? '') . ')')
                    ->defaultItems(0)
                    ->rules([
                        function () {
                            return function (string $attribute, mixed $value, \Closure $fail) {
                                if (!is_array($value)) {
                                    return;
                                }
                                $names = array_filter(array_column($value, 'name'));
                                if (count($names) !== count(array_unique($names))) {
                                    $fail(__('Sender names must be unique. Duplicate names found.'));
                                }
                            };
                        },
                    ]),

                // Repeater UI for email.pmta_servers
                Repeater::make('value')
                    ->label(__('PMTA Servers'))
                    ->visible(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'pmta_servers')
                    ->dehydrated(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'pmta_servers')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Server Name (unique ID)'))
                            ->required()
                            ->maxLength(100)
                            ->helperText(__('e.g. caspmta1, caspmta3')),

                        TextInput::make('host')
                            ->label(__('Host / IP'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('user')
                            ->label(__('SSH User'))
                            ->default('root')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('port')
                            ->label(__('SSH Port'))
                            ->numeric()
                            ->default(22)
                            ->required(),

                        TextInput::make('ssh_key')
                            ->label(__('SSH Key Path'))
                            ->maxLength(500)
                            ->helperText(__('Absolute path to SSH private key file')),

                        TextInput::make('tmp_path')
                            ->label(__('Tmp Path'))
                            ->default('/tmp-pickup')
                            ->maxLength(500),

                        TextInput::make('pickup_path')
                            ->label(__('Pickup Path'))
                            ->default('/pickup')
                            ->maxLength(500),

                        TextInput::make('virtual_mta')
                            ->label(__('Virtual MTA'))
                            ->default('all')
                            ->maxLength(100),

                        TextInput::make('bounce_domain')
                            ->label(__('Bounce Domain'))
                            ->maxLength(255)
                            ->helperText(__('e.g. bounce.example.com')),

                        TextInput::make('batch_size')
                            ->label(__('Batch Size'))
                            ->numeric()
                            ->nullable()
                            ->helperText(__('Max emails per sync run (leave empty for unlimited)')),
                    ])
                    ->columns(3)
                    ->maxItems(20)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['name'] ?? __('New Server')) . ' (' . ($state['host'] ?? '') . ')')
                    ->defaultItems(0),

                // Repeater UI for email.smtp_servers
                Repeater::make('value')
                    ->label(__('SMTP Servers'))
                    ->visible(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'smtp_servers')
                    ->dehydrated(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'smtp_servers')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Server Name (unique ID)'))
                            ->required()
                            ->maxLength(100)
                            ->helperText(__('e.g. newsletter-smtp, transactional-smtp')),

                        TextInput::make('host')
                            ->label(__('Host'))
                            ->required()
                            ->maxLength(255)
                            ->helperText(__('SMTP server hostname')),

                        TextInput::make('port')
                            ->label(__('Port'))
                            ->numeric()
                            ->default(587)
                            ->required(),

                        Select::make('encryption')
                            ->label(__('Encryption'))
                            ->options([
                                'tls'  => __('TLS (STARTTLS, port 587)'),
                                'ssl'  => __('SSL (port 465)'),
                                'none' => __('None (not recommended)'),
                            ])
                            ->default('tls')
                            ->required(),

                        TextInput::make('username')
                            ->label(__('Username'))
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label(__('Password'))
                            ->maxLength(500),

                        TextInput::make('from_address')
                            ->label(__('Default From Address'))
                            ->email()
                            ->maxLength(255),

                        TextInput::make('from_name')
                            ->label(__('Default From Name'))
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->maxItems(20)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['name'] ?? __('New SMTP Server')) . ' (' . ($state['host'] ?? '') . ':' . ($state['port'] ?? '') . ')')
                    ->defaultItems(0),

                // Repeater UI for email.domain_routing
                Repeater::make('value')
                    ->label(__('Domain Routing Rules'))
                    ->visible(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'domain_routing')
                    ->dehydrated(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'domain_routing')
                    ->schema([
                        Select::make('provider')
                            ->label(__('Email Provider'))
                            ->options([
                                'microsoft' => __('Microsoft (hotmail, outlook, live)'),
                                'yahoo'     => __('Yahoo (yahoo, ymail, aol)'),
                                'gmail'     => __('Gmail (gmail, googlemail)'),
                                'icloud'    => __('iCloud (icloud, me, mac)'),
                                'default'   => __('Default (everything else)'),
                            ])
                            ->required(),

                        Select::make('server')
                            ->label(__('PMTA Server'))
                            ->options(fn () => collect(\JanDev\EmailSystem\Support\SenderResolver::pmtaServers())
                                ->mapWithKeys(fn ($s) => [$s['name'] => $s['name'] . ' (' . ($s['host'] ?? '') . ')'])
                                ->toArray())
                            ->required()
                            ->helperText(__('Server from PMTA Servers setting')),
                    ])
                    ->columns(2)
                    ->maxItems(10)
                    ->reorderable(false)
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => ($state['provider'] ?? '') . ' → ' . ($state['server'] ?? ''))
                    ->defaultItems(0),

                // Repeater UI for email.routing_profiles
                Repeater::make('value')
                    ->label(__('Routing Profiles'))
                    ->visible(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'routing_profiles')
                    ->dehydrated(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'routing_profiles')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Profile Name (unique ID)'))
                            ->required()
                            ->maxLength(100)
                            ->rules(['regex:/^[a-zA-Z0-9_-]+$/'])
                            ->helperText(__('e.g. casino-routing, pmta4-routing')),

                        Repeater::make('rules')
                            ->label(__('Routing Rules'))
                            ->schema([
                                Select::make('provider')
                                    ->label(__('Email Provider'))
                                    ->options([
                                        'microsoft' => __('Microsoft (hotmail, outlook, live)'),
                                        'yahoo'     => __('Yahoo (yahoo, ymail, aol)'),
                                        'gmail'     => __('Gmail (gmail, googlemail)'),
                                        'icloud'    => __('iCloud (icloud, me, mac)'),
                                        'default'   => __('Default (everything else)'),
                                    ])
                                    ->required(),

                                Select::make('server')
                                    ->label(__('PMTA Server'))
                                    ->options(fn () => collect(\JanDev\EmailSystem\Support\SenderResolver::pmtaServers())
                                        ->mapWithKeys(fn ($s) => [$s['name'] => $s['name'] . ' (' . ($s['host'] ?? '') . ')'])
                                        ->toArray())
                                    ->required(),
                            ])
                            ->columns(2)
                            ->maxItems(10)
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => ($state['provider'] ?? '') . ' → ' . ($state['server'] ?? '')),
                    ])
                    ->columns(1)
                    ->maxItems(20)
                    ->reorderable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? __('New Profile'))
                    ->defaultItems(0),

                // Section UI for email.send_config
                Section::make(__('Send Settings'))
                    ->visible(fn (Get $get): bool => $get('group') === 'email' && $get('key') === 'send_config')
                    ->schema([
                        TextInput::make('send_max_per_run')
                            ->label(__('Max emails per run'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(10000)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['max_per_run'] ?? 100))
                            ->dehydrateStateUsing(fn ($state) => $state)
                            ->helperText(__('How many emails to process per scheduled run (runs every 5 min)')),

                        TextInput::make('send_delay_seconds')
                            ->label(__('SMTP delay (seconds)'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(60)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['delay_seconds'] ?? 1))
                            ->dehydrateStateUsing(fn ($state) => $state)
                            ->helperText(__('Delay between individual SMTP sends')),

                        TextInput::make('send_mailgun_batch_size')
                            ->label(__('Mailgun batch size'))
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(1000)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['mailgun_batch_size'] ?? 500))
                            ->dehydrateStateUsing(fn ($state) => $state)
                            ->helperText(__('Recipients per Mailgun API call (max 1000)')),

                        TextInput::make('send_mailgun_batch_delay_ms')
                            ->label(__('Mailgun batch delay (ms)'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(60000)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['mailgun_batch_delay_ms'] ?? 2000))
                            ->dehydrateStateUsing(fn ($state) => $state)
                            ->helperText(__('Delay between batches in milliseconds')),
                    ])
                    ->columns(2),

                // Section UI for parkfly.config (single JSON object — typed fields, not a repeater)
                // Uses afterStateHydrated to extract fields from JSON, dehydrateStateUsing to write back
                Section::make(__('Parkfly Settings'))
                    ->visible(fn (Get $get): bool => $get('group') === 'parkfly' && $get('key') === 'config')
                    ->dehydrated(fn (Get $get): bool => $get('group') === 'parkfly' && $get('key') === 'config')
                    ->schema([
                        TextInput::make('parkfly_maxhely')
                            ->label(__('Max Parking Spaces (maxhely)'))
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['maxhely'] ?? null))
                            ->dehydrateStateUsing(fn ($state) => $state)
                            ->helperText(__('Total number of available parking spots')),

                        TextInput::make('parkfly_folia_ar')
                            ->label(__('Foil Wrap Price (folia_ar)'))
                            ->numeric()
                            ->required()
                            ->suffix('HUF')
                            ->minValue(0)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['folia_ar'] ?? null))
                            ->dehydrateStateUsing(fn ($state) => $state),

                        TextInput::make('parkfly_kulso_mosas')
                            ->label(__('Exterior Wash Price (kulso_mosas)'))
                            ->numeric()
                            ->required()
                            ->suffix('HUF')
                            ->minValue(0)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['kulso_mosas'] ?? null))
                            ->dehydrateStateUsing(fn ($state) => $state),

                        TextInput::make('parkfly_belso_mosas')
                            ->label(__('Interior Wash Price (belso_mosas)'))
                            ->numeric()
                            ->required()
                            ->suffix('HUF')
                            ->minValue(0)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['belso_mosas'] ?? null))
                            ->dehydrateStateUsing(fn ($state) => $state),

                        Toggle::make('parkfly_van_mosas')
                            ->label(__('Car Wash Available (van_mosas)'))
                            ->afterStateHydrated(fn ($component, $record) => $component->state((bool) ($record?->value['van_mosas'] ?? false)))
                            ->dehydrateStateUsing(fn ($state) => (int) $state)
                            ->helperText(__('Enable/disable car wash service')),

                        TextInput::make('parkfly_minimum_voucher')
                            ->label(__('Minimum Voucher Amount (minimum_voucher)'))
                            ->numeric()
                            ->required()
                            ->suffix('HUF')
                            ->minValue(0)
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->value['minimum_voucher'] ?? null))
                            ->dehydrateStateUsing(fn ($state) => $state),
                    ])
                    ->columns(2),

                // JSON textarea for all other settings
                Textarea::make('value')
                    ->label(__('Value (JSON)'))
                    ->visible(fn (Get $get): bool => !static::isRepeaterRecord($get('group') ?? '', $get('key') ?? ''))
                    ->dehydrated(fn (Get $get): bool => !static::isRepeaterRecord($get('group') ?? '', $get('key') ?? ''))
                    ->required()
                    ->rows(8)
                    ->helperText(__('JSON value. Must be valid JSON (string, number, array or object). Examples: "text", 42, true, [], {}'))
                    ->rules([
                        function () {
                            return function (string $attribute, mixed $value, \Closure $fail) {
                                if ($value === null || $value === '') {
                                    return;
                                }
                                if (is_array($value)) {
                                    return;
                                }
                                json_decode($value);
                                if (json_last_error() !== JSON_ERROR_NONE) {
                                    $fail(__('The value must be valid JSON.'));
                                }
                            };
                        },
                    ])
                    ->dehydrateStateUsing(fn ($state) => $state)
                    ->afterStateHydrated(function ($component, $state) {
                        // Skip conversion if a Repeater/Section handles this record
                        $record = $component->getRecord();
                        if ($record instanceof \JanDev\UserManagement\Models\Setting
                            && static::isRepeaterRecord($record->group, $record->key)) {
                            return;
                        }
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
                    ->wrap()
                    ->getStateUsing(function (Setting $record): string {
                        $value = $record->value;
                        if (is_array($value)) {
                            if ($record->group === 'audience' && $record->key === 'custom_fields') {
                                return collect($value)->pluck('name')->filter()->implode(', ');
                            }
                            if ($record->group === 'email' && $record->key === 'senders') {
                                return collect($value)->pluck('name')->filter()->implode(', ');
                            }
                            if ($record->group === 'email' && $record->key === 'pmta_servers') {
                                return collect($value)->pluck('name')->filter()->implode(', ');
                            }
                            if ($record->group === 'email' && $record->key === 'smtp_servers') {
                                return collect($value)->map(fn ($s) => ($s['name'] ?? '') . ' (' . ($s['host'] ?? '') . ':' . ($s['port'] ?? '') . ')')->implode(', ');
                            }
                            if ($record->group === 'email' && $record->key === 'domain_routing') {
                                return collect($value)->map(fn ($r) => ($r['provider'] ?? '') . ' → ' . ($r['server'] ?? ''))->implode(', ');
                            }
                            if ($record->group === 'email' && $record->key === 'routing_profiles') {
                                return collect($value)->pluck('name')->filter()->implode(', ');
                            }
                            if ($record->group === 'email' && $record->key === 'send_config') {
                                return 'max/run: ' . ($value['max_per_run'] ?? '?')
                                    . ', delay: ' . ($value['delay_seconds'] ?? '?') . 's'
                                    . ', batch: ' . ($value['mailgun_batch_size'] ?? '?')
                                    . ', batch_delay: ' . ($value['mailgun_batch_delay_ms'] ?? '?') . 'ms';
                            }
                            if ($record->group === 'parkfly' && $record->key === 'config') {
                                return 'maxhely: ' . ($value['maxhely'] ?? '?')
                                    . ', folia: ' . ($value['folia_ar'] ?? '?') . ' HUF'
                                    . ', mosás: ' . (($value['van_mosas'] ?? false) ? 'igen' : 'nem');
                            }
                            return json_encode($value);
                        }
                        return is_string($value) ? $value : (string) $value;
                    }),

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
