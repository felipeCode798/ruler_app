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
                    ->maxLength(255),
                Forms\Components\TextInput::make('commission_course')
                    ->maxLength(255),
                Forms\Components\TextInput::make('renewal_commission')
                    ->maxLength(255),
                Forms\Components\TextInput::make('coercive_collection_commission')
                    ->maxLength(255),
                Forms\Components\TextInput::make('commission_debit')
                    ->maxLength(255),
                Forms\Components\TextInput::make('not_resolutions_commission')
                    ->maxLength(255),
                Forms\Components\TextInput::make('payment_agreements_commission')
                    ->maxLength(255),
                Forms\Components\TextInput::make('prescriptions_commission')
                    ->maxLength(255),
                Forms\Components\TextInput::make('subpoena_commission')
                    ->maxLength(255),
                Forms\Components\TextInput::make('license_commission')
                    ->maxLength(255),
                Forms\Components\TextInput::make('pins_commission')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('commissions_controversy')
            ->columns([
                Tables\Columns\TextColumn::make('commissions_controversy'),
                Tables\Columns\TextColumn::make('commission_course'),
                Tables\Columns\TextColumn::make('renewal_commission'),
                Tables\Columns\TextColumn::make('coercive_collection_commission'),
                Tables\Columns\TextColumn::make('commission_debit'),
                Tables\Columns\TextColumn::make('not_resolutions_commission'),
                Tables\Columns\TextColumn::make('payment_agreements_commission'),
                Tables\Columns\TextColumn::make('prescriptions_commission'),
                Tables\Columns\TextColumn::make('subpoena_commission'),
                Tables\Columns\TextColumn::make('license_commission'),
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
