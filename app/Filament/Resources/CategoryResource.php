<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Percent;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationLabel = 'Categorias';
    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {

        function valueCategories(Get $get, Set $set)
        {
            $value_subpoena = $get('value_subpoena');
            $value_honorarios = $get('fee');

            $percent = Percent::first();;
            $tabulated = $percent->tabulated;


            $value_total_subpoena = intval($value_subpoena/2) + $tabulated + $value_honorarios;
            $set('value_total_des', intval($value_subpoena/2));
            $set('price', $value_total_subpoena);
        }

        function valueCiaDes(Get $get, Set $set)
        {
            $value_cia = $get('value_cia');
            $cia_des = $get('cia_des');
            $descuento = $value_cia * ($cia_des / 100);
            $value_total_cia = $value_cia - intval($descuento);
            $set('value_cia_des', $value_total_cia);
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Información De La Categoria')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Categoria')
                        ->required()
                        ->maxLength(191),
                    Forms\Components\TextInput::make('value_subpoena')
                        ->label('Valor Comparendo')
                        ->required()
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => valueCategories($get, $set))
                        ->afterStateHydrated(fn (Get $get, Set $set) => valueCategories($get, $set))
                        ->prefix('$'),
                    Forms\Components\TextInput::make('fee')
                        ->label('Valor Honorarios')
                        ->required()
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => valueCategories($get, $set))
                        ->afterStateHydrated(fn (Get $get, Set $set) => valueCategories($get, $set))
                        ->prefix('$'),
                    Forms\Components\TextInput::make('value_total_des')
                        ->label('Valor Con Descuento')
                        ->required()
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => valueCategories($get, $set))
                        ->afterStateHydrated(fn (Get $get, Set $set) => valueCategories($get, $set))
                        ->prefix('$'),
                ]),
                Forms\Components\Section::make('Información De La Categoria')
                ->columns(4)
                ->schema([
                    Forms\Components\TextInput::make('value_transport')
                        ->label('Valor Transito')
                        ->required()
                        ->numeric()
                        ->prefix('$'),
                    Forms\Components\TextInput::make('value_cia')
                        ->label('Valor CIA')
                        ->required()
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => valueCiaDes($get, $set))
                        ->afterStateHydrated(fn (Get $get, Set $set) => valueCiaDes($get, $set))
                        ->prefix('$'),
                    Forms\Components\TextInput::make('cia_des')
                        ->label('CIA Con Descuento')
                        ->required()
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => valueCiaDes($get, $set))
                        ->afterStateHydrated(fn (Get $get, Set $set) => valueCiaDes($get, $set))
                        ->suffix('%'),
                    Forms\Components\TextInput::make('value_cia_des')
                        ->label('Valor CIA Descuento')
                        ->required()
                        ->numeric()
                        ->prefix('$'),

                ]),
                Forms\Components\Section::make('Precio De La Categoria')
                ->columns(1)
                ->schema([
                    Forms\Components\TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->live()
                    ->prefix('$'),
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
                Tables\Columns\TextColumn::make('value_subpoena')
                    ->label('Valor Comparendo')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('USD')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
