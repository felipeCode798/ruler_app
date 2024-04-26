<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountingResource\Pages;
use App\Filament\Resources\AccountingResource\RelationManagers;
use App\Models\Accounting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use App\Models\ProcessReturn;
use Illuminate\Support\Facades\DB;
use App\Models\ProcessingCommission;
use App\Models\User;
use App\Models\Licenses;
use App\Models\Pins;
use App\Models\Debit;
use App\Models\CoerciveCollection;
use App\Models\NotResolutions;
use App\Models\PaymentAgreement;
use App\Models\Prescription;
use App\Models\Subpoena;
use App\Models\Controversy;
use App\Models\Course;
use App\Models\Renewall;
use App\Models\Expense;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class AccountingResource extends Resource
{
    protected static ?string $model = Accounting::class;
    protected static ?string $navigationLabel = 'Contabilidad';
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    public static function form(Form $form): Form
    {

        function calculateRevenue(Set $set, Get $get) {

            $dateStart = $get('date_start');
            $dateEnd = $get('date_end');

            $revenue = 0;

            $revenue += Licenses::where('licenses.created_at', '>=', $dateStart)
                ->where('licenses.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('payments', 'licenses.id', '=', 'payments.licenses_id')
                ->sum('value');

            $revenue += Pins::where('pins.created_at', '>=', $dateStart)
                ->where('pins.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('pins_payments', 'pins.id', '=', 'pins_payments.pins_id')
                ->sum('value');

            $revenue += Debit::where('debits.created_at', '>=', $dateStart)
                ->where('debits.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('debit_payments', 'debits.id', '=', 'debit_payments.debit_id')
                ->sum('debit_payments.value');

            $revenue += CoerciveCollection::where('coercive_collections.created_at', '>=', $dateStart)
                ->where('coercive_collections.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('coercive_collection_payments', 'coercive_collections.id', '=', 'coercive_collection_payments.coercive_collection_id')
                ->sum('coercive_collection_payments.value');

            $revenue += NotResolutions::where('not_resolutions.created_at', '>=', $dateStart)
                ->where('not_resolutions.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('not_resolution_payments', 'not_resolutions.id', '=', 'not_resolution_payments.not_resolutions_id')
                ->sum('not_resolution_payments.value');

            $revenue += PaymentAgreement::where('payment_agreements.created_at', '>=', $dateStart)
                ->where('payment_agreements.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('payment_agreement_payments', 'payment_agreements.id', '=', 'payment_agreement_payments.payment_agreement_id')
                ->sum('payment_agreement_payments.value');

            $revenue += Prescription::where('prescriptions.created_at', '>=', $dateStart)
                ->where('prescriptions.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('prescription_payments', 'prescriptions.id', '=', 'prescription_payments.prescription_id')
                ->sum('prescription_payments.value');

            $revenue += Subpoena::where('subpoenas.created_at', '>=', $dateStart)
                ->where('subpoenas.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('subpoena_payments', 'subpoenas.id', '=', 'subpoena_payments.subpoena_id')
                ->sum('subpoena_payments.value');

            $revenue += Controversy::where('controversies.created_at', '>=', $dateStart)
                ->where('controversies.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('controversy_payments', 'controversies.id', '=', 'controversy_payments.controversy_id')
                ->sum('controversy_payments.value');

            $revenue += Course::where('courses.created_at', '>=', $dateStart)
                ->where('courses.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('course_payments', 'courses.id', '=', 'course_payments.course_id')
                ->sum('course_payments.value');

            $revenue += Renewall::where('renewalls.created_at', '>=', $dateStart)
                ->where('renewalls.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereNotIn('state', ['return'])
                ->join('renewall_payments', 'renewalls.id', '=', 'renewall_payments.renewall_id')
                ->sum('renewall_payments.value');

            $set('revenue', $revenue);

        }

        function calculatExpenses(Set $set, Get $get) {

            $dateStart = $get('date_start');
            $dateEnd = $get('date_end');

            $expenses = 0;

            $expenses += Licenses::where('licenses.created_at', '>=', $dateStart)
                ->where('licenses.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('payments', 'licenses.id', '=', 'payments.licenses_id')
                ->sum('value');

            $expenses += Pins::where('pins.created_at', '>=', $dateStart)
                ->where('pins.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('pins_payments', 'pins.id', '=', 'pins_payments.pins_id')
                ->sum('value');

            $expenses += Debit::where('debits.created_at', '>=', $dateStart)
                ->where('debits.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('debit_payments', 'debits.id', '=', 'debit_payments.debit_id')
                ->sum('debit_payments.value');

            $expenses += CoerciveCollection::where('coercive_collections.created_at', '>=', $dateStart)
                ->where('coercive_collections.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('coercive_collection_payments', 'coercive_collections.id', '=', 'coercive_collection_payments.coercive_collection_id')
                ->sum('coercive_collection_payments.value');

            $expenses += NotResolutions::where('not_resolutions.created_at', '>=', $dateStart)
                ->where('not_resolutions.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('not_resolution_payments', 'not_resolutions.id', '=', 'not_resolution_payments.not_resolutions_id')
                ->sum('not_resolution_payments.value');

            $expenses += PaymentAgreement::where('payment_agreements.created_at', '>=', $dateStart)
                ->where('payment_agreements.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('payment_agreement_payments', 'payment_agreements.id', '=', 'payment_agreement_payments.payment_agreement_id')
                ->sum('payment_agreement_payments.value');

            $expenses += Prescription::where('prescriptions.created_at', '>=', $dateStart)
                ->where('prescriptions.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('prescription_payments', 'prescriptions.id', '=', 'prescription_payments.prescription_id')
                ->sum('prescription_payments.value');

            $expenses += Subpoena::where('subpoenas.created_at', '>=', $dateStart)
                ->where('subpoenas.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('subpoena_payments', 'subpoenas.id', '=', 'subpoena_payments.subpoena_id')
                ->sum('subpoena_payments.value');

            $expenses += Controversy::where('controversies.created_at', '>=', $dateStart)
                ->where('controversies.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('controversy_payments', 'controversies.id', '=', 'controversy_payments.controversy_id')
                ->sum('controversy_payments.value');

            $expenses += Course::where('courses.created_at', '>=', $dateStart)
                ->where('courses.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('course_payments', 'courses.id', '=', 'course_payments.course_id')
                ->sum('course_payments.value');

            $expenses += Renewall::where('renewalls.created_at', '>=', $dateStart)
                ->where('renewalls.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->whereIn('state', ['return'])
                ->join('renewall_payments', 'renewalls.id', '=', 'renewall_payments.renewall_id')
                ->sum('renewall_payments.value');

            $expenses += Expense::where('expenses.created_at', '>=', $dateStart)
                ->where('expenses.created_at', '<', date('Y-m-d', strtotime($dateEnd . ' +1 day')))
                ->sum('amount');

            $set('expenses', $expenses);
        }

        function calculateTotal(Set $set, Get $get) {

            $revenue = $get('revenue');
            $expenses = $get('expenses');

            $total_value = $revenue - $expenses;

            $set('total_value', $total_value);

        }


        return $form
            ->schema([
                Forms\Components\Section::make('Rango de Fechas')
                ->columns(2)
                ->schema([
                    Forms\Components\DatePicker::make('date_start')
                        ->label('Fecha de Inicio')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $set('date_end', '');
                            $set('revenue', '');
                            $set('expenses', '');
                            $set('total_value', '');
                        }),
                    Forms\Components\DatePicker::make('date_end')
                        ->label('Fecha de Fin')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateRevenue($set, $get);
                            calculatExpenses($set, $get);
                            calculateTotal($set, $get);
                        })
                ]),
                Forms\Components\Section::make('Gastos e Ingresos')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('revenue')
                        ->prefix('$')
                        ->label('Ingresos')
                        ->required()
                        ->live()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('expenses')
                        ->prefix('$')
                        ->label('Egresos')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('total_value')
                        ->prefix('$')
                        ->label('Valor Total')
                        ->required()
                        ->maxLength(255),
                ]),
                Forms\Components\Section::make('Descripción')
                ->columns(1)
                ->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Descripción')
                        ->required()
                        ->maxLength(255),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date_start')
                    ->label('Fecha de Inicio')
                    ->dateTime('m/d/Y')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_end')
                    ->label('Fecha de Fin')
                    ->dateTime('m/d/Y')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('revenue')
                    ->label('Ingresos')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expenses')
                    ->label('Egresos')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                ->form([
                    DatePicker::make('date_start')
                        ->label('Fecha de Inicio'),
                    DatePicker::make('date_end')
                        ->label('Fecha de Fin'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['date_start'],
                            fn (Builder $query, $date) => $query->whereDate('date_start', '>=', $date),
                        )
                        ->when(
                            $data['date_end'],
                            fn (Builder $query, $date) => $query->whereDate('date_end', '<=', $date),
                        );
                }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
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
