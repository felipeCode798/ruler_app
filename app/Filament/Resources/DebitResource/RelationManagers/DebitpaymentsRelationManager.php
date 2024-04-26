<?php

namespace App\Filament\Resources\DebitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Models\DebitPayments;

class DebitpaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'debitpayments';
    protected static ?string $title = 'Crear Pago';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Comparendo')
                    ->required()
                    ->columnSpan('full')
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->columnSpan('full')
                    ->maxLength(255),
                Forms\Components\TextInput::make('value')
                    ->label('Valor Abonado')
                    ->required()
                    ->columnSpan('full')
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre'),
                Tables\Columns\TextColumn::make('description')->label('Descripción'),
                Tables\Columns\TextColumn::make('value')->label('Valor Abonado'),
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
