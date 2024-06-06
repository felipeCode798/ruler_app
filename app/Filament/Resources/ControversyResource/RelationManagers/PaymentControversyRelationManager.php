<?php

namespace App\Filament\Resources\ControversyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PaymentControversyRelationManager extends RelationManager
{
    protected static string $relationship = 'PaymentControversy';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('concept')
                    ->required()
                    ->options([
                        'Salida' => 'Salida',
                        'Entrada' => 'Entrada',
                    ]),
                Forms\Components\Select::make('method_payment')
                    ->required()
                    ->options([
                        'Efectivo' => 'Efectivo',
                        'Traferencia' => 'Traferencia',
                        'Datafono' => 'Datafono',
                        'Sistecredito' => 'Sistecredito',
                    ]),
                Forms\Components\TextInput::make('reference')
                    ->maxLength(255),
                Forms\Components\TextInput::make('value')
                    ->label('Valor')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Observaciones')
                    ->maxLength(255)
                    ->columnSpan('full'),
                Forms\Components\Hidden::make('responsible_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('concept')
            ->columns([
                Tables\Columns\TextColumn::make('concept')
                    ->label('Concepto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('method_payment')
                    ->label('Metodo de Pago')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Referencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
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
