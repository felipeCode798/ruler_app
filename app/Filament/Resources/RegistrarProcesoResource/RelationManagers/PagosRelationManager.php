<?php

namespace App\Filament\Resources\RegistrarProcesoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Facades\DB;
use App\Models\Proceso;
use App\Models\RegistrarProceso;
use Illuminate\Support\Facades\Auth;

class PagosRelationManager extends RelationManager
{
    protected static string $relationship = 'Pagos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('proceso_id')
                    ->label('Proceso')
                    ->default($procesoId),
                Forms\Components\Select::make('registrar_procesos_id')
                    ->label('Proceso')
                    ->options(function ($get) {
                        $procesoId = $this->ownerRecord->id;
                        $proceso = Proceso::find($procesoId);
                        if ($proceso) {
                            return $proceso->getRelatedProcessCategories()->pluck('name', 'id');
                        }
                        return [];
                    })
                    ->required(),
                Forms\Components\Select::make('concepto')
                    ->label('Concepto')
                    ->options([
                        'Salida' => 'Salida',
                        'Entrada' => 'Entrada',
                    ])
                    ->required(),
                Forms\Components\Select::make('metodo_pago')
                    ->label('Metodo De Pago')
                    ->options([
                        'Efectivo' => 'Efectivo',
                        'Transferencia' => 'Transferencia',
                        'Datafono' => 'Datafono',
                        'Sistecredito' => 'Sistecredito',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('descripcion')
                    ->label('Descripcion')
                    ->maxLength(255),
                Forms\Components\TextInput::make('referencia')
                    ->label('Referencia')
                    ->maxLength(255),
                Forms\Components\TextInput::make('valor')
                    ->label('Valor')
                    ->required()
                    ->numeric(),
                Forms\Components\Hidden::make('responsible_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('registrar_procesos_id')
            ->columns([
                Tables\Columns\TextColumn::make('registrarProceso.processCategory.name')
                    ->label('Proceso')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('concepto')
                    ->label('Concepto')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('metodo_pago')
                    ->label('Método de Pago')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('pagado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
