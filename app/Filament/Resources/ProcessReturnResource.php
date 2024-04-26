<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcessReturnResource\Pages;
use App\Filament\Resources\ProcessReturnResource\RelationManagers;
use App\Models\ProcessReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Set;
use  Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

use App\Models\Licenses;

class ProcessReturnResource extends Resource
{
    protected static ?string $model = ProcessReturn::class;
    protected static ?string $navigationLabel = 'Devoluciones';
    protected static ?string $navigationIcon = 'heroicon-o-receipt-refund';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('dni')
                        ->label('Nombre de Usuario')
                        ->columnSpan('full')
                        ->disabled(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre de Usuario')
                        ->disabled(),
                    Forms\Components\TextInput::make('email')
                        ->label('Email de Usuario')
                        ->disabled(),
                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono de Usuario')
                        ->disabled(),
                ])->afterStateHydrated(function (Set $set, Get $get) {
                    $user = User::find($get('user_id'));
                    $set('dni', $user->dni ?? '');
                    $set('name', $user->name ?? '');
                    $set('email', $user->email ?? '');
                    $set('phone', $user->phone ?? '');
                }),
                Forms\Components\Section::make('Tramite de la Renovación')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('state')
                    ->label('Estado')
                    ->columnSpan('full')
                    ->disabled(),
                    Forms\Components\TextInput::make('total_value')
                        ->prefix('$')
                        ->label('Valor total')
                        ->disabled(),
                    Forms\Components\TextInput::make('observations')
                        ->label('Observaciones')
                        ->disabled(),
                ])->afterStateHydrated(function (Set $set, Get $get) {
                    $processReturn = ProcessReturn::find($get('id'));
                    $data = $processReturn ? $processReturn->processData() : null;
                    if ($data) {
                        $set('state', $data->state ?? '');
                        $set('total_value', $data->total_value ?? '');
                        $set('observations', $data->observations ?? '');
                        $set('paid', $data->paid ?? false);
                    }
                }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type_process')
                    ->label('Proceso')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['user.name','type_process']),
                    ]),
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
            'index' => Pages\ListProcessReturns::route('/'),
            //'create' => Pages\CreateProcessReturn::route('/create'),
            //'edit' => Pages\EditProcessReturn::route('/{record}/edit'),
        ];
    }
}
