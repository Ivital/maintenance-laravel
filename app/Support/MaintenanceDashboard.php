<?php

declare(strict_types=1);

namespace App\Support;

use Liberu\Modules\Maintenance\Assets\Models\Asset;
use Liberu\Modules\Maintenance\Inspections\Models\Inspection;
use Liberu\Modules\Maintenance\Inventory\Models\StockItem;
use Liberu\Modules\Maintenance\PreventativeMaintenance\Models\MaintenancePlan;
use Liberu\Modules\Maintenance\Procurement\Models\PurchaseRequest;
use Liberu\Modules\Maintenance\Procurement\Models\VendorContract;
use Liberu\Modules\Maintenance\Scheduling\Models\ScheduleEntry;
use Liberu\Modules\Maintenance\WorkOrders\Models\WorkOrder;

final class MaintenanceDashboard
{
    /**
     * @return array{
     *     open_work_orders:int,
     *     overdue_work_orders:int,
     *     work_orders_due_7_days:int,
     *     active_assets:int,
     *     assets_under_maintenance:int,
     *     overdue_maintenance_plans:int,
     *     maintenance_plans_due_7_days:int,
     *     draft_inspections:int,
     *     low_stock_items:int,
     *     out_of_stock_items:int,
     *     pending_purchase_requests:int,
     *     vendor_contracts_expiring_30_days:int,
     *     scheduled_today:int
     * }
     */
    public function summary(int $teamId): array
    {
        $workOrders = WorkOrder::query()->where('team_id', $teamId);
        $assets = Asset::query()->where('team_id', $teamId);
        $plans = MaintenancePlan::query()->where('team_id', $teamId);
        $inspections = Inspection::query()->where('team_id', $teamId);
        $stock = StockItem::query()->where('team_id', $teamId);
        $purchases = PurchaseRequest::query()->where('team_id', $teamId);
        $contracts = VendorContract::query()->where('team_id', $teamId);
        $schedule = ScheduleEntry::query()->where('team_id', $teamId);

        return [
            'open_work_orders' => (clone $workOrders)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),

            'overdue_work_orders' => (clone $workOrders)
                ->overdue()
                ->count(),

            'work_orders_due_7_days' => (clone $workOrders)
                ->dueWithin(7)
                ->count(),

            'active_assets' => (clone $assets)
                ->active()
                ->count(),

            'assets_under_maintenance' => (clone $assets)
                ->underMaintenance()
                ->count(),

            'overdue_maintenance_plans' => (clone $plans)
                ->overdue()
                ->count(),

            'maintenance_plans_due_7_days' => (clone $plans)
                ->dueSoon(7)
                ->count(),

            'draft_inspections' => (clone $inspections)
                ->draft()
                ->count(),

            'low_stock_items' => (clone $stock)
                ->lowStock()
                ->count(),

            'out_of_stock_items' => (clone $stock)
                ->outOfStock()
                ->count(),

            'pending_purchase_requests' => (clone $purchases)
                ->pending()
                ->count(),

            'vendor_contracts_expiring_30_days' => (clone $contracts)
                ->expiringSoon(30)
                ->count(),

            'scheduled_today' => (clone $schedule)
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->where(function ($query): void {
                    $query
                        ->whereBetween('starts_at', [now()->startOfDay(), now()->endOfDay()])
                        ->orWhereBetween('next_due_at', [now()->startOfDay(), now()->endOfDay()]);
                })
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function workOrderStatusCounts(int $teamId): array
    {
        $counts = WorkOrder::query()
            ->where('team_id', $teamId)
            ->selectRaw('status, COUNT(*) AS aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statuses = [
            'requested',
            'triaged',
            'in_progress',
            'blocked',
            'completed',
            'cancelled',
        ];

        $result = [];

        foreach ($statuses as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
        }

        return $result;
    }
}
