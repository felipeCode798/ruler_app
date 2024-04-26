<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PercentResource\Pages;
use App\Filament\Resources\PercentResource\RelationManagers;
use App\Models\Percent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PercentResource extends Resource
{
    protected static ?string $model = Percent::class;
    protected static ?string $navigationLabel = 'Porcentajes de procesos';
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Configuración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Porcentajes de procesos')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('coercive_collection')
                        ->label('Cobro Coactivo')
                        ->suffix('%')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('debit')
                        ->label('Adeudo')
                        ->suffix('%')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('not_resolutions')
                        ->label('Sin Resoluciones')
                        ->suffix('%')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('payment_agreements')
                        ->label('Acuerdos de Pago')
                        ->suffix('%')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('prescriptions')
                        ->label('Prescripciones')
                        ->suffix('%')
                        ->numeric()
                        ->default(null),
                    Forms\Components\TextInput::make('subpoena')
                        ->label('Comparendo')
                        ->suffix('%')
                        ->numeric()
                        ->default(null),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('coercive_collection')
                    ->label('Cobro Coactivo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('debit')
                    ->label('Adeudo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('not_resolutions')
                    ->label('Sin Resoluciones')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_agreements')
                    ->label('Acuerdos de Pago')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prescriptions')
                    ->label('Prescripciones')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subpoena')
                    ->label('Comparendo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
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
            'index' => Pages\ListPercents::route('/'),
            'create' => Pages\CreatePercent::route('/create'),
            'edit' => Pages\EditPercent::route('/{record}/edit'),
        ];
    }
}
