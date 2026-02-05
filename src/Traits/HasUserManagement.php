<?php

namespace JanDev\UserManagement\Traits;

use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

trait HasUserManagement
{
    use HasRoles;

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Super admin always has access
        if ($this->hasRole('super-admin')) {
            return true;
        }

        // Check for 'access admin' permission
        return $this->hasPermissionTo('access admin');
    }
}
