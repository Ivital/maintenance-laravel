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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Foundation\RolesPermissions\Models\Role;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

function dashboardTeam(string $name = 'Dashboard Team', bool $admin = false): array
{
    $user = User::factory()->create();

    $team = new Team();
    $team->forceFill([
        'name' => $name,
        'personal_team' => false,
        'user_id' => $user->getKey(),
    ])->save();

    $user->teams()->syncWithoutDetaching([$team->getKey()]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();

    if ($admin) {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($team->getKey());

        $role = Role::findOrCreate(
            (string) config('filament-shield.super_admin.name', 'super_admin'),
            'web',
        );

        $user->assignRole($role);

        $registrar->forgetCachedPermissions();
    }

    return [$user, $team];
}

function invokeDashboardMethod(object $object, string $method): mixed
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke($object);
}

it('builds tenant-scoped operational maintenance dashboard metrics', function (): void {
    [, $team] = dashboardTeam();

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
    [, $firstTeam] = dashboardTeam('First');

    $this->seed(MaintenanceDemoSeeder::class);

    [, $secondTeam] = dashboardTeam('Second');

    $summary = app(MaintenanceDashboard::class)->summary((int) $secondTeam->getKey());

    expect(array_sum($summary))->toBe(0)
        ->and($firstTeam->getKey())->not->toBe($secondTeam->getKey());
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

it('renders the tenant operational dashboard shell with every widget mounted', function (): void {
    [$user, $team] = dashboardTeam(admin: true);

    $this->seed(MaintenanceDemoSeeder::class);

    $this->actingAs($user)
        ->get("/admin/{$team->getKey()}")
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('MaintenanceOverviewStats')
        ->assertSee('MaintenanceRiskStats')
        ->assertSee('WorkOrderStatusChart')
        ->assertSee('UpcomingWorkOrders')
        ->assertSee('UpcomingMaintenanceSchedule');
});

it('renders the two operational table widgets for the active tenant', function (): void {
    [$user, $team] = dashboardTeam();

    $this->seed(MaintenanceDemoSeeder::class);

    $this->actingAs($user);
    Filament::setTenant($team, isQuiet: true);

    Livewire::test(UpcomingWorkOrders::class)
        ->assertOk()
        ->assertSee('Найближчі та прострочені наряди')
        ->assertSee('DEMO-WO-0001')
        ->assertSee('[DEMO] Перевірити вібрацію насосного агрегату');

    Livewire::test(UpcomingMaintenanceSchedule::class)
        ->assertOk()
        ->assertSee('Графік найближчих робіт')
        ->assertSee('[DEMO] Плановий огляд насоса');
});

it('handles missing tenant defensively in dashboard widgets', function (): void {
    auth()->logout();
    Filament::setTenant(null, isQuiet: true);

    expect(invokeDashboardMethod(new MaintenanceOverviewStats(), 'getStats'))->toBe([])
        ->and(invokeDashboardMethod(new MaintenanceRiskStats(), 'getStats'))->toBe([])
        ->and(invokeDashboardMethod(new WorkOrderStatusChart(), 'getData'))->toBe([
            'datasets' => [],
            'labels' => [],
        ])
        ->and(invokeDashboardMethod(new WorkOrderStatusChart(), 'getType'))->toBe('doughnut');

    $workOrdersQuery = invokeDashboardMethod(new UpcomingWorkOrders(), 'query');
    $scheduleQuery = invokeDashboardMethod(new UpcomingMaintenanceSchedule(), 'query');

    expect($workOrdersQuery)->toBeInstanceOf(Builder::class)
        ->and($workOrdersQuery->count())->toBe(0)
        ->and($scheduleQuery)->toBeInstanceOf(Builder::class)
        ->and($scheduleQuery->count())->toBe(0);
});

it('exposes populated widget datasets for the active tenant', function (): void {
    [$user, $team] = dashboardTeam();

    $this->seed(MaintenanceDemoSeeder::class);

    $this->actingAs($user);
    Filament::setTenant($team, isQuiet: true);

    expect(invokeDashboardMethod(new MaintenanceOverviewStats(), 'getStats'))->toHaveCount(6)
        ->and(invokeDashboardMethod(new MaintenanceRiskStats(), 'getStats'))->toHaveCount(7);

    $chart = invokeDashboardMethod(new WorkOrderStatusChart(), 'getData');

    expect($chart['labels'])->toHaveCount(6)
        ->and($chart['datasets'][0]['data'])->toBe([1, 0, 1, 0, 1, 0]);

    $workOrdersQuery = invokeDashboardMethod(new UpcomingWorkOrders(), 'query');
    $scheduleQuery = invokeDashboardMethod(new UpcomingMaintenanceSchedule(), 'query');

    expect($workOrdersQuery->count())->toBe(2)
        ->and($scheduleQuery->count())->toBe(1);
});
