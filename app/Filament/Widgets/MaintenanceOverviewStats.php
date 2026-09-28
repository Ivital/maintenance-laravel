<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\MaintenanceDashboard;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenanceOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Операційний стан ТОіР';

    protected ?string $description = 'Ключові показники поточного стану робіт, обладнання та ППР';

    protected function getStats(): array
    {
        $teamId = $this->teamId();

        if ($teamId === null) {
            return [];
        }

        $summary = app(MaintenanceDashboard::class)->summary($teamId);

        return [
            Stat::make('Відкриті наряди', $summary['open_work_orders'])
                ->icon('heroicon-o-wrench-screwdriver')
                ->color($summary['open_work_orders'] > 0 ? 'primary' : 'success'),

            Stat::make('Прострочені наряди', $summary['overdue_work_orders'])
                ->icon('heroicon-o-exclamation-triangle')
                ->color($summary['overdue_work_orders'] > 0 ? 'danger' : 'success'),

            Stat::make('Наряди на 7 днів', $summary['work_orders_due_7_days'])
                ->icon('heroicon-o-calendar-days')
                ->color('info'),

            Stat::make('Активне обладнання', $summary['active_assets'])
                ->icon('heroicon-o-cube')
                ->color('success'),

            Stat::make('ППР прострочено', $summary['overdue_maintenance_plans'])
                ->icon('heroicon-o-arrow-path')
                ->color($summary['overdue_maintenance_plans'] > 0 ? 'danger' : 'success'),

            Stat::make('ППР на 7 днів', $summary['maintenance_plans_due_7_days'])
                ->icon('heroicon-o-clock')
                ->color('warning'),
        ];
    }

    private function teamId(): ?int
    {
        $team = Filament::getTenant() ?? auth()->user()?->currentTeam;

        return $team === null ? null : (int) $team->getKey();
    }
}
