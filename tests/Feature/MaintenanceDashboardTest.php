<?php

declare(strict_types=1);

use App\Filament\Widgets\MaintenanceOverviewStats;
use App\Filament\Widgets\MaintenanceRiskStats;
use App\Filament\Widgets\UpcomingMaintenanceSchedule;
use App\Filament\Widgets\UpcomingWorkOrders;
use App\Filament\Widgets\WorkOrderStatusChart;
use App\Models\User;
use App\Support\MaintenanceDashboard;
use Database\Seeders\MaintenanceDemoSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;

uses(RefreshDatabase::class);

it('builds tenant-scoped operational maintenance dashboard metrics', function (): void {
    $user = User::factory()->create();

    $team = new Team();
    $team->forceFill([
        'name' => 'Dashboard Team',
        'personal_team' => false,
        'user_id' => $user->getKey(),
    ])->save();

    $user->teams()->syncWithoutDetaching([$team->getKey()]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();

    $this->seed(MaintenanceDemoSeeder::class);

    $summary = app(MaintenanceDashboard::class)->summary((int) $team->getKey());

    expect($summary)
        ->toMatchArray([
            'open_work_orders' => 2,
            'overdue_work_orders' => 0,
            'work_orders_due_7_days' => 2,
            'active_assets' => 3,
            'assets_under_maintenance' => 0,
            'overdue_maintenance_plans' => 0,
            'maintenance_plans_due_7_days' => 0,
            'draft_inspections' => 1,
            'low_stock_items' => 1,
            'out_of_stock_items' => 0,
            'pending_purchase_requests' => 1,
            'scheduled_today' => 0,
        ]);

    expect(app(MaintenanceDashboard::class)->workOrderStatusCounts((int) $team->getKey()))
        ->toBe([
            'requested' => 1,
            'triaged' => 0,
            'in_progress' => 1,
            'blocked' => 0,
            'completed' => 1,
            'cancelled' => 0,
        ]);
});

it('keeps dashboard metrics isolated by team', function (): void {
    $firstUser = User::factory()->create();
    $firstTeam = new Team();
    $firstTeam->forceFill([
        'name' => 'First',
        'personal_team' => false,
        'user_id' => $firstUser->getKey(),
    ])->save();
    $firstUser->teams()->syncWithoutDetaching([$firstTeam->getKey()]);
    $firstUser->forceFill(['current_team_id' => $firstTeam->getKey()])->save();

    $this->seed(MaintenanceDemoSeeder::class);

    $secondUser = User::factory()->create();
    $secondTeam = new Team();
    $secondTeam->forceFill([
        'name' => 'Second',
        'personal_team' => false,
        'user_id' => $secondUser->getKey(),
    ])->save();

    $summary = app(MaintenanceDashboard::class)->summary((int) $secondTeam->getKey());

    expect(array_sum($summary))->toBe(0);
});

it('discovers the operational dashboard widgets in the admin panel', function (): void {
    $panel = Filament::getPanel('admin');

    expect($panel->getWidgets())
        ->toContain(
            MaintenanceOverviewStats::class,
            MaintenanceRiskStats::class,
            WorkOrderStatusChart::class,
            UpcomingWorkOrders::class,
            UpcomingMaintenanceSchedule::class,
        )
        ->not->toContain(Filament\Widgets\FilamentInfoWidget::class);
});
