<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentAgreementResource\Pages;
use App\Filament\Resources\PaymentAgreementResource\RelationManagers;
use App\Models\PaymentAgreement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ProcessReturn;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Forms\Get;
use Filament\Forms\Set;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\ActionGroup;

class PaymentAgreementResource extends Resource
{
    protected static ?string $model = PaymentAgreement::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';
    protected static ?string $navigationLabel = 'Acuerdos de Pago';
    protected static ?string $navigationGroup = 'Revocatorias';

    public static function form(Form $form): Form
    {
        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function calculateTotalValues(Set $set, Get $get) {
            $procents = Percent::getCoerciveCollectionPercentage();

            $payment = $get('value_received') * $procents;
            $payment = round($payment);
            $set('value', $payment);

            $gainsTotal = ($get('total_value') - $payment);
            $set('total_debit', $payment);
            $set('total_gains', $gainsTotal);
        }

        function calculateCommission(Set $set, Get $get) {
            $processor = User::find($get('processor_id'));
            $procents = Percent::getCoerciveCollectionPercentage();

            if ($processor) {
                $commission = $processor->processingCommissions->first()->payment_agreements_commission ?? 0;
                $payment = $get('value_received') * $procents;
                $payment = round($payment);
                $set('value', $payment);
                $sumDebit = $get('value');

                $commissionTotal = ($get('total_value') - $sumDebit) * ($commission / 100);
                $commissionTotal = round($commissionTotal);

                $set('value_commission', $commissionTotal);
                $set('total_debit', $sumDebit + $commissionTotal);

                $set('total_gains', $get('total_value') - $sumDebit - $commissionTotal);
            } else {
                $set('value_commission', 0);

                $payment = $get('value_received') * $procents;
                $payment = round($payment);
                $sumDebit = $get('value');

                $set('total_debit', $sumDebit);
                $set('total_gains', $get('total_value'));
            }
        }


        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Cedula')
                        ->placeholder('Seleccione un documento')
                        ->relationship('client', 'dni', function ($query) {
                            $query->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'cliente');
                            });
                        })
                        ->searchable()
                        ->preload()
                        ->columnSpan('full')
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $user = User::find($get('user_id'));
                            $set('name', $user->name ?? '');
                            $set('email', $user->email ?? '');
                            $set('phone', $user->phone ?? '');
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            $user = User::find($get('user_id'));
                            $set('name', $user->name ?? '');
                            $set('email', $user->email ?? '');
                            $set('phone', $user->phone ?? '');
                        })
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->label('Nombre')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('dni')
                                ->label('Cedula')
                                ->unique(User::class, 'dni', ignoreRecord: true)
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->label('Email')
                                ->required()
                                ->email()
                                ->unique(User::class, 'dni', ignoreRecord: true)
                                ->disabledOn('edit')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->label('Teléfono')
                                ->required()
                                ->numeric()
                                ->maxLength(11),
                            Forms\Components\TextInput::make('address')
                                ->label('Dirección')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('password')
                                ->label('Contraseña')
                                ->required()
                                ->password()
                                ->hiddenOn('edit')
                                ->dehydrateStateUsing(fn ($state) => Hash::make($state)),
                            Forms\Components\Select::make('role')
                                ->label('Rol')
                                ->placeholder('Seleccione un rol')
                                ->relationship('roles', 'name')
                                ->options($roleOptions)
                                ->required(),
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')
                        ->live()
                        ->disabled()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->live()
                        ->disabled()
                        ->required()
                        ->email()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono')
                        ->live()
                        ->disabled()
                        ->required()
                        ->numeric()
                        ->maxLength(11),
                ]),
                Forms\Components\Section::make('Información del Acuerdo de Pago')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('cc')
                        ->label('A.P')
                        ->required()
                        ->columnSpan(1)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('subpoena')
                        ->label('Comparendo')
                        ->required()
                        ->columnSpan(2)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('value_received')
                        ->label('Valor Comparendo')
                        ->prefix('$')
                        ->required()
                        ->maxLength(255)
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }),
                    Forms\Components\TextInput::make('total_value')
                        ->label('Valor Recibido')
                        ->prefix('$')
                        ->required()
                        ->numeric()
                        ->live()
                        ->maxLength(11)
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }),
                    Forms\Components\TextInput::make('value')
                        ->label('Valor')
                        ->prefix('$')
                        ->required()
                        ->maxLength(255)
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }),
                ]),
                Forms\Components\Section::make('Información del Tramitador')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('processor_id')
                        ->label('Tramitador')
                        ->placeholder('Seleccione un tramitador')
                        ->relationship('processor', 'name', function ($query) {
                            $query->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'tramitador');
                            });
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        }),
                    Forms\Components\TextInput::make('value_commission')
                        ->label('Comisión')
                        ->prefix('$')
                        ->required()
                        ->numeric()
                        ->maxLength(11),
                ]),
                Forms\Components\Section::make('Tramite del Acuerdo de Pago')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('state')
                    ->label('Estado')
                    ->placeholder('Seleccione un estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'return' => 'Devuelto',
                        'ready' => 'Listo',
                    ])
                    ->required(),
                    Forms\Components\Textarea::make('observations')
                        ->label('Observaciones')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan('full'),
                    Forms\Components\Toggle::make('paid')
                        ->label('Pagado')
                        ->inline(false)
                ]),
                Forms\Components\Section::make('Valores Totales')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('total_debit')
                    ->prefix('$')
                    ->disabled()
                    ->label('Valor Debito')
                    ->live(),
                    Forms\Components\TextInput::make('total_gains')
                    ->label('Valor Ganancias')
                    ->prefix('$')
                    ->disabled()
                    ->live(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(PaymentAgreement::query()->where('state', '!=', 'return'))
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.dni')
                    ->label('Cedula')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cc')
                    ->label('CC')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor total')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum')
                    ->label('Pagos')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('paid')
                    ->label('Pagado')
                    ->toggleable(isToggledHiddenByDefault: true)
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
                ActionGroup::make([
                    Tables\Actions\Action::make('Devolucion', 'start_process')
                        ->action(function (PaymentAgreement $PaymentAgreement) {
                            ProcessReturn::create([
                                'user_id' => $PaymentAgreement->user_id,
                                'type_process' => 'payment_agreements',
                                'process_id' => $PaymentAgreement->id,
                            ]);

                            $PaymentAgreement->update(['state' => 'return']);

                            return redirect()->back();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['client.name','cc','state','total_value','payments_sum']),
                    ]),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentagreementpaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentAgreements::route('/'),
            'create' => Pages\CreatePaymentAgreement::route('/create'),
            'edit' => Pages\EditPaymentAgreement::route('/{record}/edit'),
        ];
    }
}
