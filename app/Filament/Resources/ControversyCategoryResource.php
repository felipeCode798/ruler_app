<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControversyCategoryResource\Pages;
use App\Filament\Resources\ControversyCategoryResource\RelationManagers;
use App\Models\ControversyCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ControversyCategoryResource extends Resource
{
    protected static ?string $model = ControversyCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationLabel = 'Categorías Controversia';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Categoría de Controversia';
    protected static ?string $pluralModelLabel = 'Categorías de Controversia';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(10),

                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('processor_value')
                    ->label('Tramitador')
                    ->prefix('$')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('client_value')
                    ->label('Cliente')
                    ->prefix('$')
                    ->numeric()
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('processor_value')
                    ->label('Tramitador')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_value')
                    ->label('Cliente')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index' => Pages\ListControversyCategories::route('/'),
            'create' => Pages\CreateControversyCategory::route('/create'),
            'edit' => Pages\EditControversyCategory::route('/{record}/edit'),
        ];
    }
}
