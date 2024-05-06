<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierCoursePaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'SupplierCoursePayments';
    protected static ?string $title = 'Crear Pago de Proveedor';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('name')
                    ->label('Tipo de Pago')
                    ->placeholder('Seleccione un pago')
                    ->options([
                        'CIA' => 'CIA',
                        'Transito' => 'Transito',
                        'Abogado' => 'Abogado',
                        'Impresion' => 'Impresion',
                        'Examenes' => 'Examenes',
                        'Carta Escula' => 'Carta Escula',
                        'Honorarios' => 'Honorarios',
                        'Otros' => 'Otros',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Valor')
                    ->required()
                    ->integer(),
                Forms\Components\Select::make('payment_method')
                    ->label('Metodo de Pago')
                    ->placeholder('Seleccione un metodo de pago')
                    ->options([
                        'Efectivo' => 'Efectivo',
                        'Trasferincia' => 'Trasferincia',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('payment_reference')
                    ->label('Referencia de Pago')
                    ->maxLength(255),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tipo de Pago')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metodo de Pago')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->label('Referencia de Pago')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Agregar Pago'),
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
