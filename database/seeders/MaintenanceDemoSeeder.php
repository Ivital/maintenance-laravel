<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Liberu\Foundation\Organizations\Models\Team;

class MaintenanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $team = Team::query()->firstOrFail();
        $teamId = (int) $team->getKey();
        $now = now();

        DB::transaction(function () use ($teamId, $now): void {
            /*
             * Core
             */
            DB::table('maintenance_organizations')->updateOrInsert(
                ['team_id' => $teamId, 'code' => 'DEMO'],
                [
                    'name' => '[DEMO] Основна організація',
                    'description' => 'Демонстраційна організація TOIR2',
                    'state' => 'active',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $statuses = [
                ['code' => 'NEW', 'name' => 'Нова', 'sort_order' => 10, 'is_default' => true],
                ['code' => 'IN_PROGRESS', 'name' => 'В роботі', 'sort_order' => 20, 'is_default' => false],
                ['code' => 'COMPLETED', 'name' => 'Завершена', 'sort_order' => 30, 'is_default' => false],
                ['code' => 'CANCELLED', 'name' => 'Скасована', 'sort_order' => 40, 'is_default' => false],
            ];

            foreach ($statuses as $status) {
                DB::table('maintenance_statuses')->updateOrInsert(
                    ['team_id' => $teamId, 'code' => $status['code']],
                    [
                        'name' => $status['name'],
                        'color' => null,
                        'sort_order' => $status['sort_order'],
                        'is_default' => $status['is_default'],
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            $priorities = [
                ['code' => 'LOW', 'name' => 'Низький', 'sort_order' => 10, 'is_default' => false],
                ['code' => 'NORMAL', 'name' => 'Нормальний', 'sort_order' => 20, 'is_default' => true],
                ['code' => 'HIGH', 'name' => 'Високий', 'sort_order' => 30, 'is_default' => false],
                ['code' => 'URGENT', 'name' => 'Терміновий', 'sort_order' => 40, 'is_default' => false],
            ];

            foreach ($priorities as $priority) {
                DB::table('maintenance_priorities')->updateOrInsert(
                    ['team_id' => $teamId, 'code' => $priority['code']],
                    [
                        'name' => $priority['name'],
                        'color' => null,
                        'sort_order' => $priority['sort_order'],
                        'is_default' => $priority['is_default'],
                        'is_active' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            DB::table('maintenance_numbering_sequences')->updateOrInsert(
                ['team_id' => $teamId, 'document_type' => 'work-order'],
                [
                    'prefix' => 'WO-',
                    'next_number' => 1,
                    'padding' => 6,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Customer / Site
             */
            DB::table('maintenance_customers')->updateOrInsert(
                ['team_id' => $teamId, 'code' => 'DEMO-CUSTOMER'],
                [
                    'name' => '[DEMO] Внутрішній замовник',
                    'type' => 'customer',
                    'industry' => 'Промисловість',
                    'description' => 'Тестовий замовник для перевірки TOIR2',
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            $customerId = (int) DB::table('maintenance_customers')
                ->where('team_id', $teamId)
                ->where('code', 'DEMO-CUSTOMER')
                ->value('id');

            DB::table('maintenance_sites')->updateOrInsert(
                ['team_id' => $teamId, 'code' => 'DEMO-SITE'],
                [
                    'customer_id' => $customerId,
                    'name' => '[DEMO] Основний виробничий майданчик',
                    'address' => 'Демонстраційний майданчик',
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Assets
             */
            $assets = [
                [
                    'code' => 'DEMO-PUMP-001',
                    'name' => '[DEMO] Насосний агрегат №1',
                    'category' => 'Насосне обладнання',
                    'manufacturer' => 'Demo',
                    'model' => 'P-100',
                    'location' => 'Насосна станція',
                    'condition' => 'good',
                    'criticality' => 'high',
                ],
                [
                    'code' => 'DEMO-MOTOR-001',
                    'name' => '[DEMO] Електродвигун №1',
                    'category' => 'Електрообладнання',
                    'manufacturer' => 'Demo',
                    'model' => 'M-55',
                    'location' => 'Насосна станція',
                    'condition' => 'good',
                    'criticality' => 'normal',
                ],
                [
                    'code' => 'DEMO-PANEL-001',
                    'name' => '[DEMO] Щит керування №1',
                    'category' => 'Автоматика',
                    'manufacturer' => 'Demo',
                    'model' => 'CTRL-1',
                    'location' => 'Щитова',
                    'condition' => 'fair',
                    'criticality' => 'high',
                ],
            ];

            foreach ($assets as $asset) {
                DB::table('maintenance_assets')->updateOrInsert(
                    ['team_id' => $teamId, 'code' => $asset['code']],
                    [
                        'name' => $asset['name'],
                        'description' => 'Демонстраційний актив TOIR2',
                        'category' => $asset['category'],
                        'manufacturer' => $asset['manufacturer'],
                        'model' => $asset['model'],
                        'location' => $asset['location'],
                        'condition' => $asset['condition'],
                        'criticality' => $asset['criticality'],
                        'status' => 'active',
                        'sensor_enabled' => false,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            $pumpId = (int) DB::table('maintenance_assets')
                ->where('team_id', $teamId)
                ->where('code', 'DEMO-PUMP-001')
                ->value('id');

            $motorId = (int) DB::table('maintenance_assets')
                ->where('team_id', $teamId)
                ->where('code', 'DEMO-MOTOR-001')
                ->value('id');

            /*
             * Preventive maintenance
             */
            DB::table('maintenance_preventative_plans')->updateOrInsert(
                ['team_id' => $teamId, 'code' => 'DEMO-PM-PUMP-MONTHLY'],
                [
                    'name' => '[DEMO] Щомісячне ТО насосного агрегату',
                    'description' => 'Перевірка підшипників, ущільнень, вібрації та витоків',
                    'equipment_id' => $pumpId,
                    'instructions' => 'Оглянути агрегат, перевірити кріплення та параметри роботи.',
                    'estimated_duration' => 60,
                    'frequency_unit' => 'months',
                    'frequency_value' => 1,
                    'next_due_at' => $now->copy()->addMonth(),
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Work Orders
             */
            $orders = [
                [
                    'number' => 'DEMO-WO-0001',
                    'title' => '[DEMO] Перевірити вібрацію насосного агрегату',
                    'equipment_id' => $pumpId,
                    'priority' => 'high',
                    'status' => 'requested',
                    'due_date' => $now->copy()->addDays(2),
                ],
                [
                    'number' => 'DEMO-WO-0002',
                    'title' => '[DEMO] Огляд електродвигуна',
                    'equipment_id' => $motorId,
                    'priority' => 'normal',
                    'status' => 'in_progress',
                    'due_date' => $now->copy()->addDay(),
                ],
                [
                    'number' => 'DEMO-WO-0003',
                    'title' => '[DEMO] Планове очищення обладнання',
                    'equipment_id' => $pumpId,
                    'priority' => 'low',
                    'status' => 'completed',
                    'due_date' => $now->copy()->subDay(),
                ],
            ];

            foreach ($orders as $order) {
                DB::table('maintenance_work_orders')->updateOrInsert(
                    ['team_id' => $teamId, 'number' => $order['number']],
                    [
                        'title' => $order['title'],
                        'description' => 'Демонстраційний наряд TOIR2',
                        'equipment_id' => $order['equipment_id'],
                        'customer_id' => $customerId,
                        'location' => 'Основний виробничий майданчик',
                        'priority' => $order['priority'],
                        'status' => $order['status'],
                        'due_date' => $order['due_date'],
                        'estimated_minutes' => 60,
                        'completed_at' => $order['status'] === 'completed'
                            ? $now->copy()->subHours(2)
                            : null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            $workOrderId = (int) DB::table('maintenance_work_orders')
                ->where('team_id', $teamId)
                ->where('number', 'DEMO-WO-0001')
                ->value('id');

            DB::table('maintenance_work_order_comments')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'work_order_id' => $workOrderId,
                    'comment' => '[DEMO] Потрібна перевірка перед наступною зміною.',
                ],
                [
                    'is_internal' => false,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            DB::table('maintenance_work_order_evidence')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'work_order_id' => $workOrderId,
                    'label' => '[DEMO] Контрольна точка',
                ],
                [
                    'kind' => 'note',
                    'reference' => 'DEMO',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Scheduling
             */
            DB::table('maintenance_schedule_entries')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'resource_key' => 'DEMO-PUMP-001',
                    'title' => '[DEMO] Плановий огляд насоса',
                ],
                [
                    'description' => 'Щомісячний плановий огляд',
                    'equipment_id' => $pumpId,
                    'starts_at' => $now->copy()->addDay()->setTime(9, 0),
                    'ends_at' => $now->copy()->addDay()->setTime(10, 0),
                    'status' => 'scheduled',
                    'recurrence_type' => 'monthly',
                    'recurrence_value' => 1,
                    'next_due_at' => $now->copy()->addMonth(),
                    'priority' => 'medium',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Inspections
             */
            DB::table('maintenance_inspections')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'title' => '[DEMO] Огляд насосного агрегату',
                ],
                [
                    'template_key' => 'demo-pump-inspection',
                    'status' => 'draft',
                    'outcome' => 'pending',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Inventory
             */
            $stockItems = [
                ['part_number' => 'DEMO-BRG-6205', 'name' => '[DEMO] Підшипник 6205', 'quantity' => 12, 'reorder_level' => 4],
                ['part_number' => 'DEMO-SEAL-001', 'name' => '[DEMO] Комплект ущільнень', 'quantity' => 3, 'reorder_level' => 5],
                ['part_number' => 'DEMO-OIL-001', 'name' => '[DEMO] Мастило', 'quantity' => 20, 'reorder_level' => 5],
            ];

            foreach ($stockItems as $item) {
                DB::table('maintenance_stock_items')->updateOrInsert(
                    ['team_id' => $teamId, 'part_number' => $item['part_number']],
                    [
                        'name' => $item['name'],
                        'category' => 'Запасні частини',
                        'location' => 'Основний склад',
                        'quantity' => $item['quantity'],
                        'reserved_quantity' => 0,
                        'reorder_level' => $item['reorder_level'],
                        'reorder_quantity' => 10,
                        'unit' => 'шт',
                        'unit_cost' => 0,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ],
                );
            }

            /*
             * Procurement
             */
            DB::table('maintenance_purchase_requests')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'title' => '[DEMO] Закупівля комплектів ущільнень',
                ],
                [
                    'supplier_name' => '[DEMO] Постачальник',
                    'description' => 'Поповнення мінімального складського запасу',
                    'amount' => 5000,
                    'currency' => 'UAH',
                    'status' => 'pending',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            DB::table('maintenance_vendor_contracts')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'contract_number' => 'DEMO-CONTRACT-001',
                ],
                [
                    'vendor_name' => '[DEMO] Сервісна компанія',
                    'title' => '[DEMO] Сервісне обслуговування обладнання',
                    'contract_type' => 'service',
                    'start_date' => $now->copy()->startOfYear()->toDateString(),
                    'end_date' => $now->copy()->endOfYear()->toDateString(),
                    'contract_value' => 100000,
                    'currency' => 'UAH',
                    'status' => 'active',
                    'auto_renewal' => false,
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Labor
             */
            DB::table('maintenance_time_entries')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'work_order_id' => $workOrderId,
                    'description' => '[DEMO] Діагностика насосного агрегату',
                ],
                [
                    'minutes' => 45,
                    'status' => 'pending',
                    'currency' => 'UAH',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            /*
             * Commercial / Compliance / Portals / Reporting
             */
            DB::table('maintenance_commercial_records')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'kind' => 'estimate',
                    'title' => '[DEMO] Кошторис ремонту',
                ],
                [
                    'description' => 'Демонстраційний комерційний запис',
                    'amount' => 15000,
                    'currency' => 'UAH',
                    'status' => 'draft',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            DB::table('maintenance_compliance_records')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'kind' => 'inspection',
                    'title' => '[DEMO] Перевірка відповідності',
                ],
                [
                    'description' => 'Демонстраційний запис відповідності',
                    'status' => 'draft',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            DB::table('maintenance_portal_records')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'kind' => 'request',
                    'title' => '[DEMO] Заявка через портал',
                ],
                [
                    'description' => 'Демонстраційна портальна заявка',
                    'status' => 'draft',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );

            DB::table('maintenance_reporting_records')->updateOrInsert(
                [
                    'team_id' => $teamId,
                    'kind' => 'kpi',
                    'title' => '[DEMO] Виконані роботи',
                ],
                [
                    'description' => 'Демонстраційний KPI',
                    'metric_value' => 75,
                    'period_start' => $now->copy()->startOfMonth(),
                    'period_end' => $now->copy()->endOfMonth(),
                    'status' => 'published',
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        });
    }
}
