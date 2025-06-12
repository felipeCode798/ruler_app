<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevocationCategoryResource\Pages;
use App\Filament\Resources\RevocationCategoryResource\RelationManagers;
use App\Models\RevocationCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RevocationCategoryResource extends Resource
{
    protected static ?string $model = RevocationCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Categorías Revocatorias';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $modelLabel = 'Categoría de Revocatoria';
    protected static ?string $pluralModelLabel = 'Categorías de Revocatorias';

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

                Forms\Components\TextInput::make('processor_percentage')
                    ->label('Tramitador (%)')
                    ->suffix('%')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100),

                Forms\Components\TextInput::make('client_percentage')
                    ->label('Cliente (%)')
                    ->suffix('%')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100),

                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->columnSpanFull(),

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

                Tables\Columns\TextColumn::make('processor_percentage')
                    ->label('Tramitador')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_percentage')
                    ->label('Cliente')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('observations')
                    ->label('Observaciones')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->observations),

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
            'index' => Pages\ListRevocationCategories::route('/'),
            'create' => Pages\CreateRevocationCategory::route('/create'),
            'edit' => Pages\EditRevocationCategory::route('/{record}/edit'),
        ];
    }
}
