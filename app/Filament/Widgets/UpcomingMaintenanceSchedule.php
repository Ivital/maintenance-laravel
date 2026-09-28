<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Liberu\Modules\Maintenance\Scheduling\Models\ScheduleEntry;

class UpcomingMaintenanceSchedule extends TableWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Графік найближчих робіт')
            ->description('Заплановані та поточні роботи на найближчі 30 днів')
            ->query($this->query())
            ->columns([
                TextColumn::make('title')
                    ->label('Робота')
                    ->searchable()
                    ->limit(60),

                TextColumn::make('resource_key')
                    ->label('Ресурс')
                    ->searchable(),

                TextColumn::make('priority')
                    ->label('Пріоритет')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),

                TextColumn::make('starts_at')
                    ->label('Початок')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('next_due_at')
                    ->label('Наступний строк')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('starts_at');
    }

    private function query(): Builder
    {
        $team = Filament::getTenant() ?? auth()->user()?->currentTeam;

        if ($team === null) {
            return ScheduleEntry::query()->whereRaw('1 = 0');
        }

        return ScheduleEntry::query()
            ->where('team_id', $team->getKey())
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where(function (Builder $query): void {
                $query
                    ->whereBetween('starts_at', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
                    ->orWhereBetween('next_due_at', [now()->startOfDay(), now()->addDays(30)->endOfDay()]);
            });
    }
}
