<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProcessingCommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'processingCommissions';
    protected static ?string $title = 'Crear Comisión';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('commissions_controversy')
                    ->label('Controversia')
                    ->maxLength(255),
                Forms\Components\TextInput::make('commission_course')
                    ->label('Curso')
                    ->maxLength(255),
                Forms\Components\TextInput::make('renewal_commission')
                    ->label('Renovación')
                    ->maxLength(255),
                Forms\Components\TextInput::make('coercive_collection_commission')
                    ->label('Cobro Coativo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('commission_debit')
                    ->label('Adeudo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('not_resolutions_commission')
                    ->label('Sin Resoluciones')
                    ->maxLength(255),
                Forms\Components\TextInput::make('payment_agreements_commission')
                    ->label('Acuerdos de Pago')
                    ->maxLength(255),
                Forms\Components\TextInput::make('prescriptions_commission')
                    ->label('Prescripciones')
                    ->maxLength(255),
                Forms\Components\TextInput::make('subpoena_commission')
                    ->label('Comparendo')
                    ->maxLength(255),
                Forms\Components\TextInput::make('license_commission')
                    ->label('Licencia')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pins_commission')
                    ->label('Pins')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('commissions_controversy')
            ->columns([
                Tables\Columns\TextColumn::make('commissions_controversy')
                    ->label('Controversia'),
                Tables\Columns\TextColumn::make('commission_course')
                    ->label('Curso'),
                Tables\Columns\TextColumn::make('renewal_commission')
                    ->label('Renovación'),
                Tables\Columns\TextColumn::make('coercive_collection_commission')
                    ->label('Cobro Coativo'),
                Tables\Columns\TextColumn::make('commission_debit')
                    ->label('Adeudo'),
                Tables\Columns\TextColumn::make('not_resolutions_commission')
                    ->label('Sin Resoluciones'),
                Tables\Columns\TextColumn::make('payment_agreements_commission')
                    ->label('Acuerdos de Pago'),
                Tables\Columns\TextColumn::make('prescriptions_commission')
                    ->label('Prescripciones'),
                Tables\Columns\TextColumn::make('subpoena_commission')
                    ->label('Comparendo'),
                Tables\Columns\TextColumn::make('license_commission')
                    ->label('Licencia'),
                Tables\Columns\TextColumn::make('pins_commission'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Agregar Comisión'),
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
