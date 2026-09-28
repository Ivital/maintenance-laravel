<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\MaintenanceDashboard;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenanceRiskStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Ризики та забезпечення';

    protected ?string $description = 'Склад, закупівлі, інспекції та договірні ризики';

    protected function getStats(): array
    {
        $teamId = $this->teamId();

        if ($teamId === null) {
            return [];
        }

        $summary = app(MaintenanceDashboard::class)->summary($teamId);

        return [
            Stat::make('Обладнання в ремонті', $summary['assets_under_maintenance'])
                ->icon('heroicon-o-cog-6-tooth')
                ->color($summary['assets_under_maintenance'] > 0 ? 'warning' : 'success'),

            Stat::make('Чернетки інспекцій', $summary['draft_inspections'])
                ->icon('heroicon-o-clipboard-document-check')
                ->color($summary['draft_inspections'] > 0 ? 'warning' : 'success'),

            Stat::make('Низький запас', $summary['low_stock_items'])
                ->icon('heroicon-o-archive-box')
                ->color($summary['low_stock_items'] > 0 ? 'warning' : 'success'),

            Stat::make('Немає на складі', $summary['out_of_stock_items'])
                ->icon('heroicon-o-x-circle')
                ->color($summary['out_of_stock_items'] > 0 ? 'danger' : 'success'),

            Stat::make('Заявки на закупівлю', $summary['pending_purchase_requests'])
                ->icon('heroicon-o-shopping-cart')
                ->color($summary['pending_purchase_requests'] > 0 ? 'warning' : 'success'),

            Stat::make('Контракти ≤ 30 днів', $summary['vendor_contracts_expiring_30_days'])
                ->icon('heroicon-o-document-text')
                ->color($summary['vendor_contracts_expiring_30_days'] > 0 ? 'warning' : 'success'),

            Stat::make('Роботи сьогодні', $summary['scheduled_today'])
                ->icon('heroicon-o-calendar')
                ->color('info'),
        ];
    }

    private function teamId(): ?int
    {
        $team = Filament::getTenant() ?? auth()->user()?->currentTeam;

        return $team === null ? null : (int) $team->getKey();
    }
}
