<?php

namespace JanDev\UserManagement\Console\Commands;

use Illuminate\Console\Command;
use JanDev\UserManagement\Database\Seeders\RolesAndPermissionsSeeder;

class SetupCommand extends Command
{
    protected $signature = 'user-management:setup';

    protected $description = 'Setup roles and permissions for user management';

    public function handle(): int
    {
        $this->info('Setting up roles and permissions...');

        $seeder = new RolesAndPermissionsSeeder();
        $seeder->run();

        $this->info('Roles and permissions created successfully!');

        return self::SUCCESS;
    }
}
