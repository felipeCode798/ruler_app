<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseCategoryResource\Pages;
use App\Filament\Resources\CourseCategoryResource\RelationManagers;
use App\Models\CourseCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseCategoryResource extends Resource
{
    protected static ?string $model = CourseCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Categorías de Cursos';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Categoría de Curso';
    protected static ?string $pluralModelLabel = 'Categorías de Cursos';

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

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('50% Descuento')
                    ->schema([
                        Forms\Components\TextInput::make('transit_value_50')
                            ->label('Valor Tránsito')
                            ->prefix('$')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('processor_value_50')
                            ->label('Tramitador')
                            ->prefix('$')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('client_value_50')
                            ->label('Cliente')
                            ->prefix('$')
                            ->numeric()
                            ->required(),
                    ])->columns(3),

                Forms\Components\Section::make('25% Descuento')
                    ->schema([
                        Forms\Components\TextInput::make('transit_value_25')
                            ->label('Valor Tránsito')
                            ->prefix('$')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('processor_value_25')
                            ->label('Tramitador')
                            ->prefix('$')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('client_value_25')
                            ->label('Cliente')
                            ->prefix('$')
                            ->numeric()
                            ->required(),
                    ])->columns(3),
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

                // Columnas para 50% Descuento
                Tables\Columns\TextColumn::make('transit_value_50')
                    ->label('Valor Tránsito 50%')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('processor_value_50')
                    ->label('Tramitador 50%')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_value_50')
                    ->label('Cliente 50%')
                    ->money('USD')
                    ->sortable(),

                // Columnas para 25% Descuento
                Tables\Columns\TextColumn::make('transit_value_25')
                    ->label('Valor Tránsito 25%')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('processor_value_25')
                    ->label('Tramitador 25%')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_value_25')
                    ->label('Cliente 25%')
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
            'index' => Pages\ListCourseCategories::route('/'),
            'create' => Pages\CreateCourseCategory::route('/create'),
            'edit' => Pages\EditCourseCategory::route('/{record}/edit'),
        ];
    }
}
