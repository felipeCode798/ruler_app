<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LicensesResource\Pages;
use App\Filament\Resources\LicensesResource\RelationManagers;
use App\Models\Licenses;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\ActionGroup;
use App\Models\LicensesSetupCategory;
use App\Models\SchoolSetup;
use App\Models\PinsProcess;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class LicensesResource extends Resource
{
    protected static ?string $model = Licenses::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Licencias';
    //protected static ?string $navigationGroup = 'Gestión de Licencias';

    public static function form(Form $form): Form
    {

        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function calculateTotalCategory(Set $set, Get $get) {
            $categories = $get('category');
            $total = 0;

            foreach ($categories as $categoryId) {
                $price = LicensesSetupCategory::where('name', $categoryId)->value('price');
                $total += $price;
            }

            $set('value_enlistment', $total);
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
                        ->options(LicensesSetupCategory::pluck('name', 'name')->toArray())
                        ->required()
                        ->columns(4)
                        ->live()
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalCategory($set, $get);
                        })
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
                            'Cruce Pin' => 'Cruce Pin',
                            'Guardado' => 'Guardado',
                            'Abono' => 'Abono',
                            'Pagado' => 'Pagado',
                        ])
                        ->live()
                        ->required(),
                    Forms\Components\TextInput::make('value_enlistment')
                        ->label('Valor Carta Escuela')
                        ->live()
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->numeric(),
                    Forms\Components\TextInput::make('value_enlistment_payment')
                        ->label('Valor Carta Escuela Abonado')
                        ->hidden(function (Get $get) {
                            $enlistment = $get('enlistment');
                            return $enlistment !== 'Abono';
                        })
                        ->live()
                        ->maxLength(255),
                    Forms\Components\Select::make('pins_school_process')
                        ->label('Pines')
                        ->placeholder('Seleccione una escuela')
                        ->options(PinsProcess::pluck('name','id')->toArray())
                        ->hidden(function (Get $get) {
                            $enlistment = $get('enlistment');
                            return $enlistment !== 'Cruce Pin';
                        }),
                    Forms\Components\TextInput::make('total_pins')
                        ->label('Total Pines')
                        ->live()
                        ->hidden(function (Set $set, Get $get) {
                            $enlistment = $get('enlistment');
                            if($enlistment === 'Cruce Pin'){
                                $set('total_pins', 1);
                            }
                            return $enlistment !== 'Cruce Pin';
                        })
                        ->disabled()
                        ->dehydrated()
                        ->default(0)
                        ->maxLength(255),
                ]),
                Forms\Components\Section::make('Complementos del Trámite')
                ->columns(12)
                ->schema([
                    Forms\Components\Select::make('medical_exams')
                        ->label('Exámenes médicos')
                        ->placeholder('Seleccione un estado')
                        ->options([
                            'Pendiente' => 'Pendiente',
                            'Finalizado' => 'Finalizado',
                            'Devuelto' => 'Devuelto'
                        ])
                        ->columnSpan(6)
                        ->required(),
                    Forms\Components\Select::make('impression')
                        ->label('Impresión')
                        ->placeholder('Seleccione un estado')
                        ->options([
                            'Pendiente' => 'Pendiente',
                            'Finalizado' => 'Finalizado',
                            'Devuelto' => 'Devuelto'
                        ])
                        ->columnSpan(6)
                        ->required(),
                    Forms\Components\TextInput::make('value_exams')
                        ->label('Valor exámenes')
                        ->prefix('$')
                        ->required()
                        ->numeric()
                        ->columnSpan(4)
                        ->maxLength(11)
                        ->live(),
                    Forms\Components\TextInput::make('value_impression')
                        ->label('Valor impresión')
                        ->prefix('$')
                        ->required()
                        ->numeric()
                        ->columnSpan(4)
                        ->maxLength(11)
                        ->live(),
                    Forms\Components\TextInput::make('total_value')
                        ->label('Valor A Recibir')
                        ->prefix('$')
                        ->required()
                        ->columnSpan(4)
                        ->maxLength(11)
                        ->live(),
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
                        ->live(),
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
                    Forms\Components\ToggleButtons::make('state')
                        ->label('Estado de Proceso')
                        ->inline()
                        ->default('pendiente')
                        ->required()
                        ->options([
                            'Pendiente' => 'Pendinete',
                            'En Proceso' => 'En Proceso',
                            'Finalizado' => 'Finalizado',
                            'Devuelto' => 'Devuelto'
                        ])
                        ->colors([
                            'Pendiente' => 'info',
                            'En Proceso' => 'warning',
                            'Finalizado' => 'success',
                            'Devuelto' => 'danger'
                        ])
                        ->icons([
                            'Pendiente' => 'heroicon-m-signal',
                            'En Proceso' => 'heroicon-m-wallet',
                            'Finalizado' => 'heroicon-m-check-badge',
                            'Devuelto' => 'heroicon-m-x-circle'
                        ]),
                    Forms\Components\Textarea::make('observations')
                        ->label('Observaciones')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan('full'),
                    Forms\Components\Toggle::make('paid')
                        ->label('Pagado')
                        ->inline(false),
                    Forms\Components\Hidden::make('responsible_id')
                        ->default(fn () => Auth::id()),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\SelectColumn::make('medical_exams')
                    ->label('Exámenes Médicos')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Finalizado' => 'Finalizado',
                        'Devuelto' => 'Devuelto'
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\SelectColumn::make('impression')
                    ->label('Impresión')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'Finalizado' => 'Finalizado',
                        'Devuelto' => 'Devuelto'
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\SelectColumn::make('state')
                    ->label('Estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'En Proceso' => 'En Proceso',
                        'Finalizado' => 'Finalizado',
                        'Devuelto' => 'Devuelto'
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->money('USD')
                    ->label('Valor total')
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
                        ExcelExport::make()->fromTable()->only(['client.name','client.dni','category','enlistment','medical_exams','impression','state','total_value','created_at']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LicensesPaymentRelationManager::class,
            RelationManagers\PinsLicensesRelationManager::class,
        ];
    }

    public static function getNavigationBadgeColor(): string|array|null {
        return static::getmodel()::count() > 10? 'success' : 'danger';
    }

    public static function getNavigationBadge(): ?string {
        return static::getmodel()::count();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLicenses::route('/'),
            'create' => Pages\CreateLicenses::route('/create'),
            'edit' => Pages\EditLicenses::route('/{record}/edit'),
        ];
    }
}
