<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FilterConfigurationResource\Pages;
use App\Filament\Resources\FilterConfigurationResource\RelationManagers;
use App\Models\FilterConfiguration;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FilterConfigurationResource extends Resource
{
    protected static ?string $model = FilterConfiguration::class;
    protected static ?string $navigationLabel = 'Configuración de Filtros';
    protected static ?string $navigationIcon = 'heroicon-o-funnel';
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('filter_name')
                    ->label('Nombre del Filtro')
                    ->required()
                    ->maxLength(191),
                Forms\Components\Select::make('filter_category')
                    ->label('Categoría del Filtro')
                    ->options([
                        'controversia' => 'Controversia',
                        'curso' => 'Curso',
                        'renovacion' => 'Renovación',
                        'cobro coactivo' => 'Cobro Coactivo',
                        'adeudo' => 'Adeudo',
                        'sin resolución' => 'Sin Resolución',
                        'acuerdo de pago' => 'Acuerdo de Pago',
                        'prescripción' => 'Prescripción',
                        'comparendo' => 'Comparendo',
                        'licencia' => 'Licencia',
                    ])
                    ->required()
                    ->multiple(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('filter_name')
                    ->label('Nombre del Filtro')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('filter_category')
                    ->label('Categoría del Filtro')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
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
            'index' => Pages\ListFilterConfigurations::route('/'),
            'create' => Pages\CreateFilterConfiguration::route('/create'),
            'edit' => Pages\EditFilterConfiguration::route('/{record}/edit'),
        ];
    }
}
