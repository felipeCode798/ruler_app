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
                Forms\Components\Section::make('Información de la Categoría')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre de la Categoría')
                            ->required()
                            ->maxLength(191),

                        Forms\Components\Select::make('type')
                            ->label('Tipo de Categoría')
                            ->options([
                                'normal' => 'Normal',
                                'renovation' => 'Renovación',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Precios para Categoría Normal')
                    ->columns(5)
                    ->schema([
                        Forms\Components\TextInput::make('price_exam')
                            ->label('Examen Médico')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('price_slide')
                            ->label('Lámina')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('school_letter')
                            ->label('Carta Escuela')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('price_fees')
                            ->label('Honorarios')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('price_no_course')
                            ->label('Sin Curso')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->hidden(fn ($get) => $get('type') !== 'normal'),

                Forms\Components\Section::make('Precios para Renovación - Cliente')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('price_renewal_exam_client')
                            ->label('Solo Examen')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('price_renewal_exam_slide_client')
                            ->label('Examen y Lámina')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->hidden(fn ($get) => $get('type') !== 'renovation'),

                Forms\Components\Section::make('Precios para Renovación - Tramitador')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('price_renewal_exam_processor')
                            ->label('Solo Examen')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\TextInput::make('price_renewal_exam_slide_processor')
                            ->label('Examen y Lámina')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->hidden(fn ($get) => $get('type') !== 'renovation'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Categoría')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state): string => $state === 'normal' ? 'Normal' : 'Renovación')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'normal' => 'Normal',
                        'renovation' => 'Renovación',
                    ])
                    ->label('Tipo de Categoría'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado Activo'),
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
