<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryRevocationResource\Pages;
use App\Filament\Resources\CategoryRevocationResource\RelationManagers;
use App\Models\CategoryRevocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryRevocationResource extends Resource
{
    protected static ?string $model = CategoryRevocation::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categorías de Revocatorias';
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
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

                        Forms\Components\TextInput::make('smld_value')
                            ->label('Valor SMLD')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('subpoena_value')
                            ->label('Valor Comparendo')
                            ->prefix('$')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('standard_value')
                            ->label('Valor Tabulado')
                            ->prefix('$')
                            ->required()
                            ->numeric(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('50% Descuento')
                    ->schema([
                        Forms\Components\TextInput::make('cia_value_50')
                            ->label('Valor CIA')
                            ->prefix('$')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('transit_pay_50')
                            ->label('Valor a Pagar Tránsito')
                            ->prefix('$')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('total_discount_50')
                            ->label('Valor Total Descuento')
                            ->prefix('$')
                            ->required()
                            ->numeric(),
                    ])->columns(3),

                Forms\Components\Section::make('20% Descuento')
                    ->schema([
                        Forms\Components\TextInput::make('cia_value_20')
                            ->label('Valor CIA')
                            ->prefix('$')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('transit_pay_20')
                            ->label('Valor a Pagar Tránsito')
                            ->prefix('$')
                            ->required()
                            ->numeric(),

                        Forms\Components\TextInput::make('total_discount_20')
                            ->label('Valor Total Descuento')
                            ->prefix('$')
                            ->required()
                            ->numeric(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Categoría')
                    ->searchable(),

                Tables\Columns\TextColumn::make('smld_value')
                    ->label('SMLD V')
                    ->sortable(),

                // Columnas para 50% Descuento
                Tables\Columns\TextColumn::make('subpoena_value')
                    ->label('Valor Comparendo')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cia_value_50')
                    ->label('Valor CIA')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transit_pay_50')
                    ->label('Valor Pagar Tránsito')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_discount_50')
                    ->label('Valor Total Descuento')
                    ->money('USD')
                    ->sortable(),

                // Columnas para 20% Descuento
                Tables\Columns\TextColumn::make('cia_value_20')
                    ->label('Valor CIA')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transit_pay_20')
                    ->label('Valor Pagar Tránsito')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_discount_20')
                    ->label('Valor Total Descuento')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('standard_value')
                    ->label('Tabulado')
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
            'index' => Pages\ListCategoryRevocations::route('/'),
            'create' => Pages\CreateCategoryRevocation::route('/create'),
            'edit' => Pages\EditCategoryRevocation::route('/{record}/edit'),
        ];
    }
}
