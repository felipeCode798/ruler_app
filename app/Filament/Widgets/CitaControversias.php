<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\RegistrarProceso;
use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class CitaControversias extends BaseWidget
{

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(RegistrarProceso::query()->where('processcategory_id', 7))
            ->defaultSort('cita', 'desc')
            ->filters([
                Filter::make('today')
                    ->label('Citas de Hoy')
                    ->form([
                        DatePicker::make('today')
                            ->label('Hoy')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->whereDate('cita', $data['today']);
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('proceso.user.name')
                    ->label('Nombre del Cliente')
                    ->sortable(),
                Tables\Columns\TextColumn::make('proceso.user.dni')
                    ->label('Cedula')
                    ->copyable()
                    ->copyMessage('Cedula Copiada')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cita')
                    ->label('Cita')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('Descargar', 'download')
                    ->action(function (RegistrarProceso $registrarproceso) {
                        return redirect()->route('download', [
                            'filename' => $registrarproceso->documento_dni,
                            'filename2' => $registrarproceso->documento_poder,
                        ]);
                    }),
            ]);
    }
}
