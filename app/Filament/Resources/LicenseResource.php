<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicenseResource\Pages;
use App\Filament\Resources\LicenseResource\RelationManagers;
use App\Models\Licenses;
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
use App\Models\LicensesSetupCategory;
use App\Models\SchoolSetup;
use App\Models\PinsProcess;

class LicenseResource extends Resource
{
    protected static ?string $model = Licenses::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Licencias';
    protected static ?string $navigationGroup = 'Gestión de Licencias';

    public static function form(Form $form): Form
    {

        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        $shcool = false;
        $pins = false;

        function hiddenValuesenListment(Set $set, Get $get, &$shcool, &$pins) {
            $enlistment = $get('enlistment');

            if ($enlistment === 'abono' || $enlistment === 'pagado') {
                $shcool = true;
                $pins = false;
            } else if ($enlistment === 'guardado') {
                $shcool = false;
                $pins = false;
            } else {
                $shcool = false;
                $pins = true;
            }

            $set('value_enlistment', $shcool ? '' : null);
            $set('value_enlistment_payment', $shcool ? '' : null);
            $set('pins_school_process', $pins ? '' : null);
            $set('total_pins', $pins ? '' : null);
        }

        function calculateTotalCategory(Set $set, Get $get) {
            $category = $get('category');
            $total = 0;

            foreach ($category as $value) {
                $total += $value;
            }

            $set('value_enlistment', $total);
        }

        function calculateTotalValues(Set $set, Get $get) {
            $value_exams = $get('value_exams');
            $value_impression = $get('value_impression');
            $value_category = $get('value_enlistment');
            $value_license = $get('total_value');

            $total_debit = $value_exams + $value_impression + $value_category;
            $total_gaints = $value_license - $total_debit;

            $set('total_debit', $total_debit);
            $set('total_gains', $total_gaints);

        }

        function calculateCommission(Set $set, Get $get) {
            $processor = User::find($get('processor_id'));

            if ($processor) {
                $commission = $processor->processingCommissions->first()->license_commission ?? 0;

                $value_exams = $get('value_exams');
                $value_impression = $get('value_impression');
                $value_category = $get('value_enlistment');
                $value_license = $get('total_value');

                $total_debit = $value_exams + $value_impression + $value_category;
                $total_gaints = $value_license - $total_debit;

                $commissionTotal = ($total_gaints) * ($commission / 100);
                $commissionTotal = round($commissionTotal);

                $set('value_commission', $commissionTotal);
                $set('total_debit', $total_debit + $commissionTotal);

                $set('total_gains', $total_gaints - $commissionTotal);
            } else {
                $set('value_commission', 0);

                $value_exams = $get('value_exams');
                $value_impression = $get('value_impression');
                $value_category = $get('value_enlistment');
                $value_license = $get('total_value');

                $total_debit = $value_exams + $value_impression + $value_category;
                $total_gaints = $value_license - $total_debit;

                $set('total_debit', $total_debit);
                $set('total_gains', $total_gaints);
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
                Forms\Components\Section::make('Seleccionar de Categoría')
                ->columns(1)
                ->schema([
                    Forms\Components\CheckboxList::make('category')
                    ->label('Categoría')
                    ->options(LicensesSetupCategory::pluck('name','price')->toArray())
                    ->required()
                    ->columns(4)
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        calculateTotalCategory($set, $get);
                    })
                    ->gridDirection('row'),
                ]),
                Forms\Components\Section::make('Enrronlamiento')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('school')
                        ->label('Escuela')
                        ->placeholder('Seleccione una escuela')
                        ->options(SchoolSetup::pluck('name_school','id')->toArray())
                        ->required(),
                    Forms\Components\Select::make('enlistment')
                        ->label('Enrrolamiento')
                        ->placeholder('Seleccione una enrrolamiento')
                        ->options([
                            'cruce pin' => 'Cruce Pin',
                            'guardado' => 'Guardado',
                            'abono' => 'Abono',
                            'pagado' => 'Pagado',
                        ])
                        ->live()
                        ->afterStateHydrated(function (Set $set, Get $get) use (&$shcool, &$pins) {
                            hiddenValuesenListment($set, $get, $shcool, $pins);
                        })
                        ->afterStateUpdated(function (Set $set, Get $get) use (&$shcool, &$pins) {
                            hiddenValuesenListment($set, $get, $shcool, $pins);
                        })
                        ->required(),
                    Forms\Components\TextInput::make('value_enlistment')
                        ->label('Valor Carta Escuela')
                        ->live()
                        ->hidden($shcool)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('value_enlistment_payment')
                        ->label('Valor Carta Escuela Abonado')
                        ->live()
                        ->hidden($shcool)
                        ->maxLength(255),
                    Forms\Components\Select::make('pins_school_process')
                        ->label('Pines')
                        ->placeholder('Seleccione una escuela')
                        ->options(PinsProcess::pluck('name','id')->toArray())
                        ->hidden($pins)
                        ->required(),
                    Forms\Components\TextInput::make('total_pins')
                        ->label('Total Pines')
                        ->hidden($pins)
                        ->live()
                        ->maxLength(255),
                ]),
                Forms\Components\Section::make('Complementos del Trámite')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('medical_exams')
                        ->label('Exámenes médicos')
                        ->placeholder('Seleccione un estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'return' => 'Devuelto',
                            'ready' => 'Listo',
                        ])
                        ->columnSpan(2)
                        ->required(),
                    Forms\Components\Select::make('impression')
                        ->label('Impresión')
                        ->placeholder('Seleccione un estado')
                        ->options([
                            'pending' => 'Pendiente',
                            'return' => 'Devuelto',
                            'undefined' => 'Indefinido',
                            'ready' => 'Listo',
                        ])
                        ->columnSpan(1/2)
                        ->required(),
                    Forms\Components\TextInput::make('value_exams')
                        ->label('Valor exámenes')
                        ->prefix('$')
                        ->required()
                        ->numeric()
                        ->maxLength(11)
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }),
                    Forms\Components\TextInput::make('value_impression')
                        ->label('Valor impresión')
                        ->prefix('$')
                        ->required()
                        ->numeric()
                        ->maxLength(11)
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }),
                    Forms\Components\TextInput::make('total_value')
                        ->label('Valor Licencia')
                        ->prefix('$')
                        ->required()
                        ->maxLength(11)
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
                Forms\Components\Section::make('Tramite de la Licencia')
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
                    ->prefix('$')
                    ->disabled()
                    ->label('Valor Ganancias')
                    ->live(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Licenses::query()->where('state', '!=', 'return'))
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.dni')
                    ->label('Cedula')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                    Tables\Columns\TextColumn::make('enlistment')
                    ->label('Enrrolamiento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('medical_exams')
                    ->label('Exámenes Médicos')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impression')
                    ->label('Impresión')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->money('USD')
                    ->label('Valor total')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum')
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Pagos')
                    ->searchable()
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
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('Devolucion', 'start_process')
                        ->action(function (Licenses $Licenses) {
                            ProcessReturn::create([
                                'user_id' => $Licenses->user_id,
                                'type_process' => '	licenses',
                                'process_id' => $Licenses->id,
                            ]);

                            $Licenses->update(['state' => 'return']);

                            return redirect()->back();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['client.name','category','enlistment','medical_exams','impression','state','total_value','payments_sum',]),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicense::route('/create'),
            'edit' => Pages\EditLicense::route('/{record}/edit'),
        ];
    }
}
