<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\MaintenanceDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Liberu\Foundation\Organizations\Models\Team;

uses(RefreshDatabase::class);

it('seeds maintenance demo data without creating users and is idempotent', function (): void {
    $user = User::factory()->create();

    $team = new Team();
    $team->forceFill([
        'name' => 'Default',
        'personal_team' => false,
        'user_id' => $user->getKey(),
    ])->save();

    $user->teams()->syncWithoutDetaching([$team->getKey()]);
    $user->forceFill([
        'current_team_id' => $team->getKey(),
    ])->save();

    $usersBefore = User::query()->count();

    $this->seed(MaintenanceDemoSeeder::class);

    expect(User::query()->count())->toBe($usersBefore);

    $expectedCounts = [
        'maintenance_organizations' => 1,
        'maintenance_customers' => 1,
        'maintenance_sites' => 1,
        'maintenance_assets' => 3,
        'maintenance_preventative_plans' => 1,
        'maintenance_work_orders' => 3,
        'maintenance_work_order_comments' => 1,
        'maintenance_work_order_evidence' => 1,
        'maintenance_schedule_entries' => 1,
        'maintenance_inspections' => 1,
        'maintenance_stock_items' => 3,
        'maintenance_purchase_requests' => 1,
        'maintenance_vendor_contracts' => 1,
        'maintenance_time_entries' => 1,
        'maintenance_commercial_records' => 1,
        'maintenance_compliance_records' => 1,
        'maintenance_portal_records' => 1,
        'maintenance_reporting_records' => 1,
    ];

    foreach ($expectedCounts as $table => $count) {
        expect(DB::table($table)->where('team_id', $team->getKey())->count())
            ->toBe($count);
    }

    $firstCounts = [];

    foreach ($expectedCounts as $table => $count) {
        $firstCounts[$table] = DB::table($table)
            ->where('team_id', $team->getKey())
            ->count();
    }

    $this->seed(MaintenanceDemoSeeder::class);

    expect(User::query()->count())->toBe($usersBefore);

    foreach ($firstCounts as $table => $count) {
        expect(DB::table($table)->where('team_id', $team->getKey())->count())
            ->toBe($count);
    }
});

it('keeps all seeded maintenance records inside the existing team', function (): void {
    $user = User::factory()->create();

    $team = new Team();
    $team->forceFill([
        'name' => 'Default',
        'personal_team' => false,
        'user_id' => $user->getKey(),
    ])->save();

    $user->teams()->syncWithoutDetaching([$team->getKey()]);

    $this->seed(MaintenanceDemoSeeder::class);

    $teamScopedTables = [
        'maintenance_organizations',
        'maintenance_customers',
        'maintenance_sites',
        'maintenance_assets',
        'maintenance_preventative_plans',
        'maintenance_work_orders',
        'maintenance_work_order_comments',
        'maintenance_work_order_evidence',
        'maintenance_schedule_entries',
        'maintenance_inspections',
        'maintenance_stock_items',
        'maintenance_purchase_requests',
        'maintenance_vendor_contracts',
        'maintenance_time_entries',
        'maintenance_commercial_records',
        'maintenance_compliance_records',
        'maintenance_portal_records',
        'maintenance_reporting_records',
    ];

    foreach ($teamScopedTables as $table) {
        expect(
            DB::table($table)
                ->where('team_id', '!=', $team->getKey())
                ->count()
        )->toBe(0);
    }
});
