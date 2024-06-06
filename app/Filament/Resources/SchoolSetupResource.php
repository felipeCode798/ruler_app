<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolSetupResource\Pages;
use App\Filament\Resources\SchoolSetupResource\RelationManagers;
use App\Models\SchoolSetup;
use App\Models\PinProcess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolSetupResource extends Resource
{
    protected static ?string $model = SchoolSetup::class;

    protected static ?string $navigationLabel = 'Configuración de la Escuela';
    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_school')
                    ->label('Nombre de la escuela')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('address')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('responsible')
                    ->label('Responsable')
                    ->required()
                    ->maxLength(191),
                Forms\Components\TextInput::make('total_pins')
                    ->label('Total de pines')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_school')
                    ->label('Nombre de la escuela')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Dirección')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible')
                    ->label('Responsable')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_pins')
                    ->label('Total de pines')
                    ->sortable()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PinsProcessRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolSetups::route('/'),
            'create' => Pages\CreateSchoolSetup::route('/create'),
            'edit' => Pages\EditSchoolSetup::route('/{record}/edit'),
        ];
    }
}
