<?php

declare(strict_types=1);

namespace Liberu\Modules\Maintenance\Commercial\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Liberu\Modules\Maintenance\Commercial\Filament\Resources\CommercialResource;

class CommercialFilamentPlugin implements Plugin
{
    public function getId(): string
    {
        return 'module-maintenance-commercial-filament';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([CommercialResource::class]);
    }

    public function boot(Panel $panel): void {}
}
