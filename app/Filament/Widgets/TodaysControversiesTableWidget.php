<?php

namespace App\Filament\Widgets;

use App\Models\Controversy;
use Carbon\Carbon;
use Filament\Tables\Columns\Text;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class TodaysControversiesTableWidget extends BaseWidget
{

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(Controversy::query())
            ->defaultSort('appointment', 'desc')
            ->filters([
                Filter::make('today')
                    ->label('Citas de Hoy')
                    ->form([
                        DatePicker::make('today')
                            ->label('Hoy')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereDate('appointment', $data['today']);
                    }),
            ])
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('client.name')
                    ->label('Nombre del Cliente')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('appointment')
                    ->label('Cita')
                    ->sortable(),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('Descargar', 'download')
                    ->action(function (Controversy $controversy) {
                        return redirect()->route('download', [
                            'filename' => $controversy->document_dni,
                            'filename2' => $controversy->document_power,
                        ]);
                    }),
            ]);
    }
}
