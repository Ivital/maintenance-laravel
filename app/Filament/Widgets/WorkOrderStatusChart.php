<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Support\MaintenanceDashboard;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class WorkOrderStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Наряди за статусами';

    protected ?string $description = 'Поточний розподіл усіх нарядів у межах активної команди';

    protected function getData(): array
    {
        $teamId = $this->teamId();

        if ($teamId === null) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $counts = app(MaintenanceDashboard::class)->workOrderStatusCounts($teamId);

        return [
            'datasets' => [
                [
                    'label' => 'Наряди',
                    'data' => array_values($counts),
                ],
            ],
            'labels' => [
                'Нові',
                'Тріаж',
                'В роботі',
                'Заблоковані',
                'Завершені',
                'Скасовані',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    private function teamId(): ?int
    {
        $team = Filament::getTenant() ?? auth()->user()?->currentTeam;

        return $team === null ? null : (int) $team->getKey();
    }
}
