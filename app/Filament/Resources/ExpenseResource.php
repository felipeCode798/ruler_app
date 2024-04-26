<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\ActionGroup;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;
    protected static ?string $navigationLabel = 'Gastos';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Registro de gasto')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('concept')
                        ->label('Concepto')
                        ->required()
                        ->maxLength(191),
                    Forms\Components\TextInput::make('amount')
                        ->label('Cantidad')
                        ->prefix('$')
                        ->required()
                        ->numeric(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('concept')
                    ->label('Concepto')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Cantidad')
                    ->searchable()
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
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
                ExportBulkAction::make()->exports([
                    ExcelExport::make()->fromTable()->only(['concept','amount','user.name','created_at','updated_at',]),
                ])
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
