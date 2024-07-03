<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComisionProcesosRelationManager extends RelationManager
{
    protected static string $relationship = 'ComisionProcesos';
    protected static ?string $title = 'Crear Comisión';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('controverisa')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('curso')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('renovacion')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('cobro_coactivo')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('adedudo')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('sin_resolucion')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('acuedo_pago')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('prescripcion')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('comparendo')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
                Forms\Components\TextInput::make('licencia')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('controverisa')->label('Controversia'),
                Tables\Columns\TextColumn::make('curso')->label('Curso'),
                Tables\Columns\TextColumn::make('renovacion')->label('Renovación'),
                Tables\Columns\TextColumn::make('cobro_coactivo')->label('Cobro Coactivo'),
                Tables\Columns\TextColumn::make('adedudo')->label('Adedudo'),
                Tables\Columns\TextColumn::make('sin_resolucion')->label('Sin Resolución'),
                Tables\Columns\TextColumn::make('acuedo_pago')->label('Acuerdo de Pago'),
                Tables\Columns\TextColumn::make('prescripcion')->label('Prescripción'),
                Tables\Columns\TextColumn::make('comparendo')->label('Comparendo'),
                Tables\Columns\TextColumn::make('licencia')->label('Licencia'),
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
