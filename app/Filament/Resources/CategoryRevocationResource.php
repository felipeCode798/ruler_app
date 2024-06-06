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
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Forms\Get;

class CategoryRevocationResource extends Resource
{
    protected static ?string $model = CategoryRevocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categorias';
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {

        function valueCategories(Get $get, Set $set)
        {
            $value_subpoena = $get('comparing_value');
            $value_honorarios = $get('fee_value');

            $value_total_subpoena = intval($value_subpoena/2) + 38168 + $value_honorarios;
            $set('comparing_value_discount', intval($value_subpoena/2));
            $set('price', $value_total_subpoena);
        }

        function valueCiaDes(Get $get, Set $set)
        {
            $value_cia = $get('cia_value');
            $cia_des = $get('cia_discount_value');
            $descuento = $value_cia * ($cia_des / 100);
            $value_total_cia = $value_cia - intval($descuento);
            $set('cia_total_value', $value_total_cia);
        }

        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Grid::make()
                        ->columns(4)
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->label('Categoria')
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn(string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                ->maxLength(255),
                            Forms\Components\TextInput::make('fee_value')
                                ->numeric()
                                ->prefix('$')
                                ->label('Honorarios')
                                ->default(null),
                            Forms\Components\TextInput::make('comparing_value')
                                ->required()
                                ->prefix('$')
                                ->label('Valor Comparendo')
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Get $get, Set $set) => valueCategories($get, $set))
                                ->afterStateHydrated(fn (Get $get, Set $set) => valueCategories($get, $set))
                                ->numeric(),
                            Forms\Components\TextInput::make('comparing_value_discount')
                                ->required()
                                ->prefix('$')
                                ->label('Descuento del Comparendo')
                                ->disabled()
                                ->dehydrated()
                                ->numeric(),

                        ]),
                        Forms\Components\Grid::make()
                            ->columns(4)
                            ->schema([
                                Forms\Components\TextInput::make('transit_value')
                                    ->label('Valor Transito')
                                    ->prefix('$')
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('cia_value')
                                    ->label('Valor CIA')
                                    ->prefix('$')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => valueCiaDes($get, $set))
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('cia_discount_value')
                                    ->label('Descuento CIA')
                                    ->suffix('%')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Get $get, Set $set) => valueCiaDes($get, $set))
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\TextInput::make('cia_total_value')
                                    ->label('Valor Total CIA')
                                    ->prefix('$')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('price')
                                    ->label('Precio')
                                    ->required()
                                    ->columnSpan(3)
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(255)
                                    ->unique(CategoryRevocation::class, 'slug', ignoreRecord:true),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Activo')
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Categoria')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comparing_value_discount')
                    ->label('Valor Comparendo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fee_value')
                    ->label('Honorarios')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transit_value')
                    ->label('Valor Transito')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cia_total_value')
                    ->label('Valor CIA')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
