<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingResource\Pages;
use App\Filament\Resources\AccountingResource\RelationManagers;
use App\Models\Accounting;
use App\Models\User;
use App\Models\Pagos;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class AccountingResource extends Resource
{
    protected static ?string $model = Accounting::class;

    protected static ?string $navigationLabel = 'Contabilidad';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    public static function form(Form $form): Form
    {

        $tramitadorRole = Role::where('name', 'tramitador')->first();
        $tramitadores = $tramitadorRole ? User::role($tramitadorRole)->get() : collect();

        return $form
            ->schema([
                Forms\Components\Section::make('Gastos e Ingresos')
                    ->schema([
                        Forms\Components\Repeater::make('tramitadores')
                            ->label('Gastos e Ingresos por Tramitador')
                            ->relationship('tramitadores')
                            ->schema([
                                Forms\Components\TextInput::make('responsible')
                                    ->label('Tramitador')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('revenue')
                                    ->prefix('$')
                                    ->label('Ingresos')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->live()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('expenses')
                                    ->prefix('$')
                                    ->label('Egresos')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('accointing_paymet')
                                    ->prefix('$')
                                    ->label('Resivido')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('total_value')
                                    ->prefix('$')
                                    ->label('Valor Total')
                                    ->disabled()
                                    ->required()
                                    ->dehydrated()
                                    ->maxLength(255),
                            ])
                            ->columns(5)
                            ->disableItemCreation()
                            ->deletable(false)
                            ->afterStateHydrated(function ($state, $set, $get) use ($tramitadores) {
                                $state = $tramitadores->map(function ($tramitador) {
                                    $revenue = self::getPaymentTotal($tramitador, 'entrada');
                                    $accointing_paymet = self::getPaymentAccountingTotal($tramitador, 'entrada');
                                    $expenses = self::getPaymentTotal($tramitador, 'salida');
                                    $generalexpenses = self::getExpensePaymentTotal($tramitador);
                                    $totalexpenses = $generalexpenses + $expenses + $accointing_paymet;
                                    $total_value = $revenue - $totalexpenses;

                                    return [
                                        'responsible' => $tramitador->name,
                                        'revenue' => $revenue,
                                        'expenses' => $totalexpenses,
                                        'accointing_paymet' => $accointing_paymet,
                                        'total_value' => $total_value,
                                    ];
                                })->toArray();
                                $set('tramitadores', $state);
                            })
                    ]),
                    Forms\Components\Section::make('Total')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Placeholder::make('total_revenue')
                            ->label('Total Ingresos')
                            ->content(function (Get $get, Set $set){
                                $total = 0;
                                if(!$repeaters = $get('tramitadores')) {
                                    return $total;
                                }

                                foreach($repeaters as $key => $repeater){
                                    $total += $get("tramitadores.{$key}.revenue");
                                }

                                $set('total_revenue', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Placeholder::make('total_expenses')
                            ->label('Total Egresos')
                            ->content(function (Get $get, Set $set){
                                $total = 0;
                                if(!$repeaters = $get('tramitadores')) {
                                    return $total;
                                }

                                foreach($repeaters as $key => $repeater){
                                    $total += $get("tramitadores.{$key}.expenses");
                                }
                                $set('total_expenses', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Placeholder::make('grand_accointing_paymet')
                            ->label('Valor Total Resivido')
                            ->content(function (Get $get, Set $set){
                                $total = 0;
                                if(!$repeaters = $get('tramitadores')) {
                                    return $total;
                                }

                                foreach($repeaters as $key => $repeater){
                                    $total += $get("tramitadores.{$key}.total_value");
                                }

                                $set('grand_accointing', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Placeholder::make('grand_total_value')
                            ->label('Valor Total A Pagar')
                            ->content(function (Get $get, Set $set){
                                $total = 0;
                                if(!$repeaters = $get('tramitadores')) {
                                    return $total;
                                }

                                foreach($repeaters as $key => $repeater){
                                    $total += $get("tramitadores.{$key}.total_value");
                                }

                                $set('grand_value', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Hidden::make('grand_accointing')
                            ->default(0),
                        Forms\Components\Hidden::make('total_revenue')
                            ->default(0),
                        Forms\Components\Hidden::make('total_expenses')
                            ->default(0),
                        Forms\Components\Hidden::make('grand_value')
                            ->default(0)
                    ]),
                Forms\Components\Section::make('Descripción')
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->required()
                        ->maxLength(255),
                ]),
                Forms\Components\Hidden::make('responsible_id')
                    ->default(fn () => Auth::id()),
            ]);
    }

    private static function getPaymentTotal($tramitador, $concept)
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $pagos = Pagos::where('responsible_id', $tramitador->id)
            ->where('concepto', $concept)
            //->where('pagado', false)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor');

        return $pagos ;
    }

    private static function getPaymentAccountingTotal($tramitador, $concept)
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $pagos = Pagos::where('responsible_id', $tramitador->id)
            ->where('concepto', $concept)
            ->where('pagado', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor');

        return $pagos ;
    }

    private static function getExpensePaymentTotal($tramitador)
    {
        $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();

        $paymentProcesses = Expense::where('responsible_id', $tramitador->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        return $paymentProcesses;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Ingresos')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Egresos')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('grand_value')
                    ->label('Valor Total')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Cuadre')
                    ->dateTime()
                    ->sortable(),
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
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['total_revenue','total_expenses','grand_value','created_at']),
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
            'index' => Pages\ListAccountings::route('/'),
            'create' => Pages\CreateAccounting::route('/create'),
            'edit' => Pages\EditAccounting::route('/{record}/edit'),
        ];
    }
}
