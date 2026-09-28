<?php

declare(strict_types=1);

namespace Liberu\Modules\Maintenance\Reporting\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Liberu\Modules\Maintenance\Reporting\Filament\Resources\ReportingResource;

class ReportingFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'module-maintenance-reporting-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([ReportingResource::class]);
    }

    public function boot(Panel $panel): void {}
}
