<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicensesSetupCategoryResource\Pages;
use App\Filament\Resources\LicensesSetupCategoryResource\RelationManagers;
use App\Models\LicensesSetupCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LicensesSetupCategoryResource extends Resource
{
    protected static ?string $model = LicensesSetupCategory::class;
    protected static ?string $navigationLabel = 'Categoria de Licencias';
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de la Categoria de Licencia')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('name')
                    ->label('Categoria de Licencia')
                    ->required()
                    ->maxLength(191),
                    Forms\Components\TextInput::make('price')
                        ->label('Precio')
                        ->required()
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\TextInput::make('price_renewal')
                        ->label('Precio Renovación')
                        ->required()
                        ->numeric()
                        ->prefix('$'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Categoria de Licencia')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_renewal')
                    ->label('Precio Renovación')
                    ->money()
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicensesSetupCategories::route('/'),
            'create' => Pages\CreateLicensesSetupCategory::route('/create'),
            'edit' => Pages\EditLicensesSetupCategory::route('/{record}/edit'),
        ];
    }
}
