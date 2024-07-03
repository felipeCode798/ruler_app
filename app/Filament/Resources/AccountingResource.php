<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingResource\Pages;
use App\Filament\Resources\AccountingResource\RelationManagers;
use App\Models\Accounting;
use App\Models\User;
use App\Models\Pagos;
use App\Models\Expense;
use App\Models\RegistrarProceso;
use App\Models\Proceso;
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
                                    ->label('Recibido')
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
                                    //$processExpenses = self::getProcessExpensesTotal($tramitador);
                                    $totalexpenses = $generalexpenses + $expenses;
                                    $total_value = $revenue - $totalexpenses - $accointing_paymet;

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
                    Forms\Components\Section::make('Pagos')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Placeholder::make('simit')
                            ->label('Total Simit')
                            ->content(function (Get $get, Set $set){
                                $total = self::getTotalSimits();
                                $set('total_simit', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Placeholder::make('abogado')
                            ->label('Total Abogado')
                            ->content(function (Get $get, Set $set){
                                $total = self::getTotalAbogados();
                                $set('total_abogado', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Placeholder::make('filtro')
                            ->label('Total Filtro')
                            ->content(function (Get $get, Set $set){
                                $total = self::getTotalFiltros();
                                $set('total_filtro', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Placeholder::make('comision')
                            ->label('Total Comision')
                            ->content(function (Get $get, Set $set){
                                $total = self::getTotalComision();
                                $set('total_comision', $total);
                                return Number::currency($total, 'USD');
                            }),
                        Forms\Components\Hidden::make('total_simit')
                            ->default(0),
                        Forms\Components\Hidden::make('total_abogado')
                            ->default(0),
                        Forms\Components\Hidden::make('total_filtro')
                            ->default(0),
                        Forms\Components\Hidden::make('total_comision')
                            ->default(0),
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

                                $abogados = self::getTotalAbogados();
                                $filtros = self::getTotalFiltros();
                                $comision = self::getTotalComision();
                                $simit = self::getTotalSimits();
                                $total += $abogados + $filtros + $comision + $simit;

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
                                    $total += $get("tramitadores.{$key}.accointing_paymet");
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
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor');

        return $pagos;
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

        return $pagos;
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

    private static function getProcessExpensesTotal($tramitador)
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $registrarProcesos = RegistrarProceso::where('proceso_id', $tramitador->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('simit') +
            RegistrarProceso::where('proceso_id', $tramitador->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('pago_abogado') +
            RegistrarProceso::where('proceso_id', $tramitador->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('pago_filtro');

        return $registrarProcesos;
    }

    private static function getTotalSimits()
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $totalSimits = RegistrarProceso::whereBetween('created_at', [$startDate, $endDate])
            ->sum('simit');

        return $totalSimits;
    }

    private static function getTotalAbogados()
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $totalAbogados = RegistrarProceso::whereBetween('created_at', [$startDate, $endDate])
            ->sum('pago_abogado');

        return $totalAbogados;
    }

    private static function getTotalFiltros()
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $totalFiltros = RegistrarProceso::whereBetween('created_at', [$startDate, $endDate])
            ->sum('pago_filtro');

        return $totalFiltros;
    }

    private static function getTotalComision()
    {
        $startDate = Carbon::today()->startOfDay();
        $endDate = Carbon::today()->endOfDay();

        $totalComision = Proceso::whereBetween('created_at', [$startDate, $endDate])
            ->sum('valor_comision');

        return $totalComision;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')->label('Descripción')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('revenue')->label('Ingresos')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('expenses')->label('Egresos')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('accointing_paymet')->label('Valor Recibido')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('total_value')->label('Valor Total')->searchable()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('responsible_id')
                    ->label('Tramitador')
                    ->options(User::role('tramitador')->pluck('name', 'id')->toArray()),
                Tables\Filters\Filter::make('created_at')
                    ->label('Fecha')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make()->label('Exportar seleccionados'),
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
