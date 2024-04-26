<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

use App\Models\NotResolutions;
use Carbon\Carbon;

use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class TodaysNotResolutionTableWidget extends BaseWidget
{

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        $filterDate = Carbon::today()->subDays(10)->toDateString();
        return $table
            ->query(NotResolutions::query()->whereDate('created_at', $filterDate))
            ->filters([
                Filter::make('created_at')
                ->form([
                    DatePicker::make('created_from'),
                    DatePicker::make('created_until'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                })
            ])
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('client.name')
                    ->label('Nombre del Cliente')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('state')
                    ->label('Cita')
                    ->sortable(),
            ]);
    }

    protected function calculateTenWorkingDaysAgo()
    {
        $today = Carbon::today();
        $workingDays = 0;
        $daysToSubtract = 1;

        while ($workingDays < 10) {
            $dateToCheck = $today->subDays($daysToSubtract);
            if ($dateToCheck->isWeekday()) {
                $workingDays++;
            }
            $daysToSubtract++;
        }

        return $dateToCheck->toDateString();
    }
}
