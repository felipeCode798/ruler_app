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
use Spatie\Permission\Models\Role;
use App\Models\LicensesSetupCategory;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class RenewallResource extends Resource
{
    protected static ?string $model = Renewall::class;

    protected static ?string $navigationLabel = 'Renovaciones';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    public static function form(Form $form): Form
    {

        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function calculateTotalCategory(Set $set, Get $get) {
            $categories = $get('category');
            $total = 0;

            foreach ($categories as $categoryId) {
                $price = LicensesSetupCategory::where('name', $categoryId)->value('price_renewal');
                $total += $price;
            }

            $set('total_value', $total);
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
                        ->options(LicensesSetupCategory::pluck('name','name')->toArray())
                        ->required()
                        ->columns(4)
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalCategory($set, $get);
                        })
                        ->gridDirection('row'),
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
                            Forms\Components\TextInput::make('total_value')
                                ->prefix('$')
                                ->label('Valor renovaciones')
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->numeric()
                                ->columnSpan(4)
                                ->live(),
                            Forms\Components\TextInput::make('value_exams')
                                ->label('Valor exámenes')
                                ->prefix('$')
                                ->required()
                                ->numeric()
                                ->columnSpan(4)
                                ->live(),
                            Forms\Components\TextInput::make('value_impression')
                                ->label('Valor impresión')
                                ->prefix('$')
                                ->required()
                                ->numeric()
                                ->columnSpan(4)
                                ->live(),
                        ]),
                    Forms\Components\Section::make('Documentacion de la Renovación')
                        ->columns(1)
                        ->schema([
                            Forms\Components\FileUpload::make('document_status_account')
                                ->label('Estado de Cuenta')
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
                                ->required(),
                            Forms\Components\TextInput::make('value_commission')
                                ->prefix('$')
                                ->label('Comisión')
                                ->live(),
                        ]),
                    Forms\Components\Section::make('Tramite de la Renovación')
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
                        ]),
                    Forms\Components\Hidden::make('responsible_id')
                        ->default(fn () => Auth::id()),
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
                    ->label('Celuda')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
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
                    ->label('Valor total')
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
                        ExcelExport::make()->fromTable()->only(['client.name','client.dni','category','medical_exams','impression','state','total_value','created_at']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentRenewallRelationManager::class,
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
            'index' => Pages\ListRenewalls::route('/'),
            'create' => Pages\CreateRenewall::route('/create'),
            'edit' => Pages\EditRenewall::route('/{record}/edit'),
        ];
    }
}
