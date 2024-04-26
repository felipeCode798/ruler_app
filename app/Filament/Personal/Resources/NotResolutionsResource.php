<?php

namespace App\Filament\Personal\Resources;

use App\Filament\Personal\Resources\NotResolutionsResource\Pages;
use App\Filament\Personal\Resources\NotResolutionsResource\RelationManagers;
use App\Models\NotResolutions as NotResolution;
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
use Illuminate\Support\Facades\Auth;

class NotResolutionsResource extends Resource
{
    protected static ?string $model = NotResolution::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'No Resoluciones';
    protected static ?string $navigationGroup = 'Revocatorias';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Form $form): Form
    {
        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function calculateTotalValues(Set $set, Get $get) {
            $valueReceived = $get('value_received');
            $totalValue = $get('total_value');
            $value = $get('value');

            $set('total_debit', $value);
            $set('total_gains', $totalValue - $value);
        }

        function calculateCommission(Set $set, Get $get) {
            $processor = User::find($get('processor_id'));

            if ($processor) {
                $commission = $processor->processingCommissions->first()->not_resolutions_commission ?? 0;
                $sumDebit = $get('value');

                $commissionTotal = ($get('total_value') - $sumDebit) * ($commission / 100);
                $commissionTotal = round($commissionTotal);

                $set('value_commission', $commissionTotal);
                $set('total_debit', $sumDebit + $commissionTotal);

                $set('total_gains', $get('total_value') - $sumDebit - $commissionTotal);
            } else {
                $set('value_commission', 0);
                $sumDebit = $get('value');

                $set('total_debit', $sumDebit);
                $set('total_gains', $get('total_value'));
            }
        }

        function updateValue(Set $set, Get $get) {
            $updateValue = $get('category');

            if ($updateValue == 'B') {
                $set('value', 50000);
            } else if ($updateValue == 'C') {
                $set('value', 80000);
            } else if ($updateValue == 'D') {
                $set('value', 170000);
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
                Forms\Components\Section::make('Información del Trámite Sin Resolución')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('subpoena')
                        ->label('Comparendo')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('cc')
                        ->label('SA')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('category')
                        ->label('Categoria')
                        ->placeholder('Seleccione un categoria')
                        ->options([
                            'B' => 'B',
                            'C' => 'C',
                            'D' => 'D',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            updateValue($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            updateValue($set, $get);
                        }),
                    Forms\Components\TextInput::make('value_received')
                        ->label('Valor Comparendo')
                        ->required()
                        ->live()
                        ->numeric()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }),
                    Forms\Components\TextInput::make('total_value')
                        ->label('Valor Recibido')
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
                    Forms\Components\TextInput::make('value')
                        ->label('Valor')
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
                        ->required()
                        ->numeric()
                        ->maxLength(11),
                ]),
                Forms\Components\Section::make('Tramite Sin Resolución')
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
                    ->label('Valor Debito')
                    ->live(),
                    Forms\Components\TextInput::make('total_gains')
                    ->label('Valor Ganancias')
                    ->live(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(NotResolution::query()->where('state', '!=', 'return'))
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cc')
                    ->label('Cédula')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor Total')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum')
                    ->label('Pagos')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('paid')
                    ->label('Pagado')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Iniciar Proceso', 'start_process')
                    ->action(function (NotResolution $NotResolution) {
                        ProcessReturn::create([
                            'user_id' => $NotResolution->user_id,
                            'type_process' => 'not_resolutions',
                            'process_id' => $NotResolution->id,
                        ]);

                        $NotResolution->update(['state' => 'return']);

                        return redirect()->back();
                    }),
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
            'index' => Pages\ListNotResolutions::route('/'),
            'create' => Pages\CreateNotResolutions::route('/create'),
            'edit' => Pages\EditNotResolutions::route('/{record}/edit'),
        ];
    }
}
