<?php

declare(strict_types=1);

namespace Liberu\Modules\Maintenance\Portals\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Liberu\Modules\Maintenance\Portals\Filament\Resources\PortalsResource;

class PortalsFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'module-maintenance-portals-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([PortalsResource::class]);
    }

    public function boot(Panel $panel): void {}
}
