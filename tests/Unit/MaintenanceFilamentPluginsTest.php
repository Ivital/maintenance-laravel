<?php

declare(strict_types=1);

use App\Filament\ModulePlugins;
use Filament\Panel;
use Liberu\Modules\Maintenance\Assets\Filament\Resources\AssetResource;
use Liberu\Modules\Maintenance\Commercial\Filament\Resources\CommercialResource;
use Liberu\Modules\Maintenance\Compliance\Filament\Resources\ComplianceResource;
use Liberu\Modules\Maintenance\Core\Filament\Resources\OrganizationResource;
use Liberu\Modules\Maintenance\Core\Filament\Resources\PriorityResource;
use Liberu\Modules\Maintenance\Core\Filament\Resources\ServiceSettingResource;
use Liberu\Modules\Maintenance\Core\Filament\Resources\StatusResource;
use Liberu\Modules\Maintenance\CustomersAndSites\Filament\Resources\CustomerResource;
use Liberu\Modules\Maintenance\CustomersAndSites\Filament\Resources\SiteResource;
use Liberu\Modules\Maintenance\Inspections\Filament\Resources\InspectionResource;
use Liberu\Modules\Maintenance\Inventory\Filament\Resources\StockItemResource;
use Liberu\Modules\Maintenance\LaborAndTime\Filament\Resources\TimeEntryResource;
use Liberu\Modules\Maintenance\Portals\Filament\Resources\PortalsResource;
use Liberu\Modules\Maintenance\PreventativeMaintenance\Filament\Resources\MaintenancePlanResource;
use Liberu\Modules\Maintenance\Procurement\Filament\Resources\PurchaseRequestResource;
use Liberu\Modules\Maintenance\Procurement\Filament\Resources\VendorContractResource;
use Liberu\Modules\Maintenance\Procurement\Filament\Resources\VendorEvaluationResource;
use Liberu\Modules\Maintenance\Reporting\Filament\Resources\ReportingResource;
use Liberu\Modules\Maintenance\Scheduling\Filament\Resources\ScheduleEntryResource;
use Liberu\Modules\Maintenance\WorkOrders\Filament\Resources\WorkOrderResource;

/** @return list<string> */
function maintenanceFilamentPluginIds(): array
{
    return [
        'maintenance-core',
        'maintenance-assets',
        'maintenance-customers-and-sites',
        'maintenance-work-orders',
        'maintenance-scheduling',
        'maintenance-preventative-maintenance',
        'module-maintenance-inspections',
        'maintenance-inventory',
        'maintenance-procurement',
        'maintenance-labor-and-time',
        'module-maintenance-commercial-filament',
        'module-maintenance-compliance-filament',
        'module-maintenance-portals-filament',
        'module-maintenance-reporting-filament',
    ];
}

/** @return list<class-string> */
function maintenanceFilamentResourceClasses(): array
{
    return [
        OrganizationResource::class,
        StatusResource::class,
        PriorityResource::class,
        ServiceSettingResource::class,
        AssetResource::class,
        CustomerResource::class,
        SiteResource::class,
        WorkOrderResource::class,
        ScheduleEntryResource::class,
        MaintenancePlanResource::class,
        InspectionResource::class,
        StockItemResource::class,
        PurchaseRequestResource::class,
        VendorContractResource::class,
        VendorEvaluationResource::class,
        TimeEntryResource::class,
        CommercialResource::class,
        ComplianceResource::class,
        PortalsResource::class,
        ReportingResource::class,
    ];
}

it('composes every maintenance Filament plugin for admin and app panels', function (): void {
    foreach (['admin', 'app'] as $panelName) {
        $ids = collect(app(ModulePlugins::class)->forPanel($panelName))
            ->map->getId()
            ->all();

        expect($ids)->toContain(...maintenanceFilamentPluginIds());
    }
});

it('registers every maintenance resource through the composed plugins', function (): void {
    foreach (['admin', 'app'] as $panelName) {
        $panel = Panel::make()->id("maintenance-{$panelName}-test");

        foreach (app(ModulePlugins::class)->forPanel($panelName) as $plugin) {
            if (! in_array($plugin->getId(), maintenanceFilamentPluginIds(), true)) {
                continue;
            }

            $plugin->register($panel);
        }

        expect($panel->getResources())->toContain(...maintenanceFilamentResourceClasses());
    }
});
