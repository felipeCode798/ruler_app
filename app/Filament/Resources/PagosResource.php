<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PagosResource\Pages;
use App\Filament\Resources\PagosResource\RelationManagers;
use App\Models\Pagos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;

class PagosResource extends Resource
{
    protected static ?string $model = Pagos::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('proceso_id')
                    ->relationship('proceso', 'id')
                    ->required(),
                Forms\Components\Select::make('registrar_proceso_id')
                    ->relationship('registrarProceso', 'id')
                    ->required(),
                Forms\Components\TextInput::make('concepto')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descripcion')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('metodo_pago')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('referencia')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('valor')
                    ->required()
                    ->numeric(),
                Forms\Components\Toggle::make('pagado')
                    ->required(),
                Forms\Components\Hidden::make('responsible_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registrarProceso.processCategory.name')
                    ->label('Proceso')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('concepto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('metodo_pago')
                    ->searchable(),
                Tables\Columns\TextColumn::make('referencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('valor')
                    ->prefix('$')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('pagado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DateTimePicker::make('start_datetime')
                            ->label('Fecha y Hora de inicio'),
                        DateTimePicker::make('end_datetime')
                            ->label('Fecha y Hora de fin'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['start_datetime']) && !empty($data['end_datetime'])) {
                            return $query->whereBetween('created_at', [$data['start_datetime'], $data['end_datetime']]);
                        }
                        return $query;
                    })
                    ->label('Rango de Fecha y Hora'),

                Filter::make('pagado')
                    ->query(fn (Builder $query): Builder => $query->where('pagado', false))
                    ->label('No Pagado'),

                    Filter::make('metodo_pago')
                    ->form([
                        Select::make('metodo_pago')
                            ->label('Método de Pago')
                            ->options(Pagos::query()->distinct()->pluck('metodo_pago', 'metodo_pago'))
                            ->searchable()
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['metodo_pago'])) {
                            return $query->where('metodo_pago', $data['metodo_pago']);
                        }
                        return $query;
                    })
                    ->label('Método de Pago'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsPaid')
                        ->label('Marcar como Pagado')
                        ->action(function ($records) {
                            Pagos::whereIn('id', $records->pluck('id')->toArray())->update(['pagado' => true]);
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagos::route('/'),
            'create' => Pages\CreatePagos::route('/create'),
            'edit' => Pages\EditPagos::route('/{record}/edit'),
        ];
    }
}
