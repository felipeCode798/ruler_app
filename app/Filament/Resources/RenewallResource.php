<?php

namespace App\Filament\Resources;


use App\Filament\Resources\RenewallResource\Pages;
use App\Filament\Resources\RenewallResource\RelationManagers;
use App\Models\Renewall;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ProcessReturn;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\ProcessingCommission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\ActionGroup;
use App\Models\LicensesSetupCategory;

class RenewallResource extends Resource
{
    protected static ?string $model = Renewall::class;
    protected static ?string $navigationLabel = 'Renovaciones';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {

        function calculateTotalCategory(Set $set, Get $get) {
            $category = $get('category');
            $total = 0;

            foreach ($category as $value) {
                $total += $value;
            }

            $set('total_value', $total);
        }



        function calculateTotalValues(Set $set, Get $get) {
            $sumDebit = $get('value_exams') + $get('value_impression');
            $gainsTotal = ($get('total_value') - $sumDebit);
            $set('total_debit', $sumDebit);
            $set('total_gains', $gainsTotal);
        }

        function calculateCommission(Set $set, Get $get) {
            $processor = User::find($get('processor_id'));

            if ($processor) {
                $commission = $processor->processingCommissions->first()->renewal_commission ?? 0;

                $sumDebit = $get('value_exams') + $get('value_impression');
                $commissionTotal = ($get('total_value') - $sumDebit) * ($commission / 100);
                $commissionTotal = round($commissionTotal);

                $set('value_commission', $commissionTotal);
                $set('total_debit', $sumDebit + $commissionTotal);

                $set('total_gains', $get('total_value') - $sumDebit - $commissionTotal);
            } else {
                $set('value_commission', 0);

                $sumDebit = $get('value_exams') + $get('value_impression');
                $set('total_debit', $sumDebit);
                $set('total_gains', $get('total_value'));
            }
        }


        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();
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
                    ->options(LicensesSetupCategory::pluck('name','price_renewal')->toArray())
                    ->required()
                    ->columns(4)
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        calculateTotalCategory($set, $get);
                    })
                    ->gridDirection('row'),
                ]),
            Forms\Components\Section::make('Complementos del Trámite')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('medical_exams')
                    ->label('Exámenes médicos')
                    ->placeholder('Seleccione un estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'ready' => 'Listo',
                        'return' => 'Devuelto',
                    ])
                    ->columnSpan(2)
                    ->required(),
                    Forms\Components\Select::make('impression')
                    ->label('Impresión')
                    ->placeholder('Seleccione un estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'paid' => 'Pagado',
                        'ready' => 'Listo',
                        'return' => 'Devuelto',
                    ])
                    ->columnSpan(1/2)
                    ->required(),
                    Forms\Components\TextInput::make('total_value')
                    ->prefix('$')
                    ->label('Valor renovaciones')
                    ->required()
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(
                        function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }
                    )
                    ->afterStateHydrated(
                        function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }
                    ),
                    Forms\Components\TextInput::make('value_exams')
                    ->label('Valor exámenes')
                    ->prefix('$')
                    ->required()
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(
                        function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }
                    )
                    ->afterStateHydrated(
                        function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }
                    ),
                    Forms\Components\TextInput::make('value_impression')
                    ->label('Valor impresión')
                    ->prefix('$')
                    ->required()
                    ->numeric()
                    ->live()
                    ->afterStateUpdated(
                        function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }
                    )
                    ->afterStateHydrated(
                        function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }
                    ),

                ]),
                Forms\Components\Section::make('Documentacion de la Renovación')
                ->columns(1)
                ->schema([
                    Forms\Components\FileUpload::make('document_status_account')
                        ->label('Estado de Cuenta')
                        ->required()
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable(false)
                        ->uploadingMessage('Cargando Archivo...')
                        ->maxSize(2048),
                ]),
                Forms\Components\Section::make('Información del Tramitador')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('processor_id')
                        ->label('Tramitador')
                        ->placeholder('Seleccione un tramitador')
                        ->relationship('processor', 'name', function ($query) {$query->whereHas('roles', function ($roleQuery) {$roleQuery->where('name', 'tramitador');});})
                        ->live()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        }),
                    Forms\Components\TextInput::make('value_commission')
                        ->prefix('$')
                        ->label('Comisión')
                        ->live(),
                ]),
                Forms\Components\Section::make('Tramite de la Renovación')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('state')
                        ->label('Estado')
                        ->placeholder('Seleccione un estado')
                        ->options([
                            'pending' => 'Pendiente',
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
                    ->label('Valor Debito')
                    ->prefix('$')
                    ->disabled()
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
            ->query(Renewall::query()->where('state', '!=', 'return'))
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.dni')
                    ->label('Celuda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('medical_exams')
                    ->label('Exámenes médicos')
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
                    ->label('Valor total')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum')
                    ->label('Pagado')
                    ->money('USD')
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
                        ->action(function (Renewall $Renewall) {
                            ProcessReturn::create([
                                'user_id' => $Renewall->user_id,
                                'type_process' => 'renewalls',
                                'process_id' => $Renewall->id,
                            ]);

                            $Renewall->update(['state' => 'return']);

                            return redirect()->back();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['client.name','category','medical_exams','impression','state','value_renewals']),
                    ]),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RenewallpaymentsRelationManager::class,
            RelationManagers\SupplierRenewallPaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRenewalls::route('/'),
            'create' => Pages\CreateRenewall::route('/create'),
            'edit' => Pages\EditRenewall::route('/{record}/edit'),
        ];
    }


}
