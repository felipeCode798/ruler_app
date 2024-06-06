<?php

namespace App\Filament\Resources\LicensesResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\SchoolSetup;

class PinsLicensesRelationManager extends RelationManager
{
    protected static string $relationship = 'PinsLicenses';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('school_setup_id')
                    ->label('Seeciona Una Escuela')
                    ->searchable()
                    ->preload()
                    ->relationship('schoolsetup', 'name_school')
                    ->afterStateUpdated(fn ($state) => $this->updatePinsProcessesOptions($state))
                    ->reactive(),
                Forms\Components\Select::make('pins_processes_id')
                    ->label('Selecciona el pin Asignado')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->relationship('schoolsetup.pinsProcess', 'name'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
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
