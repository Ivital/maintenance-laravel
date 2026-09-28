<?php

declare(strict_types=1);

namespace Liberu\Modules\Maintenance\Compliance\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Liberu\Modules\Maintenance\Compliance\Filament\Resources\ComplianceResource;

class ComplianceFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'module-maintenance-compliance-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([ComplianceResource::class]);
    }

    public function boot(Panel $panel): void {}
}
