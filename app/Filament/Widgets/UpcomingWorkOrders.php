<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Liberu\Modules\Maintenance\WorkOrders\Models\WorkOrder;

class UpcomingWorkOrders extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Найближчі та прострочені наряди')
            ->description('Відкриті наряди з найближчими строками виконання')
            ->query($this->query())
            ->columns([
                TextColumn::make('number')
                    ->label('Номер')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Наряд')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('priority')
                    ->label('Пріоритет')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),

                TextColumn::make('due_date')
                    ->label('Строк')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('due_date');
    }

    private function query(): Builder
    {
        $team = Filament::getTenant() ?? auth()->user()?->currentTeam;

        if ($team === null) {
            return WorkOrder::query()->whereRaw('1 = 0');
        }

        return WorkOrder::query()
            ->where('team_id', $team->getKey())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('due_date');
    }
}
