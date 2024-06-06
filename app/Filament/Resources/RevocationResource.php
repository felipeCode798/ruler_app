<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevocationResource\Pages;
use App\Filament\Resources\RevocationResource\RelationManagers;
use App\Models\Revocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\ToggleButtons;
use App\Models\Process;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Number;
use App\Models\User;
use App\Models\Lawyer;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class RevocationResource extends Resource
{
    protected static ?string $model = Revocation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Revocatorias';

    public static function form(Form $form): Form
    {

        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Informacion del Proceso')->schema([
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
                        ])->required(),
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
                    ])->columns(3),
                    Forms\Components\Section::make('Procesos')->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('processcategory_id')
                                    ->label('Proceseso de Categoria')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(3)
                                    ->relationship('processcategory', 'name')
                                    ->reactive(),
                                Forms\Components\Select::make('categoryrevocation_id')
                                    ->label('Categoria')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(3)
                                    ->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return $processCategoryId !== '3';
                                    })
                                    ->relationship('categoryrevocation', 'name'),
                                Forms\Components\Select::make('lawyer_id')
                                    ->label('Abogado')
                                    ->searchable()
                                    ->preload()
                                    ->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return $processCategoryId !== '2';
                                    })
                                    ->columnSpan(3)
                                    ->relationship('lawyer', 'name'),
                                Forms\Components\Select::make('filter_id')
                                    ->label('Filtro')
                                    ->searchable()
                                    ->preload()
                                    ->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return $processCategoryId !== '2';
                                    })
                                    ->columnSpan(3)
                                    ->relationship('filter', 'name'),
                                Forms\Components\TextInput::make('cc')
                                    ->label('C.C')
                                    ->columnSpan(4)
                                    ->reactive()
                                    ->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return $processCategoryId === '3' || $processCategoryId === '2' || $processCategoryId === null;
                                    })
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('sa')
                                    ->label('S.A')
                                    ->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');

                                        if($processCategoryId === '2'){
                                            return false;
                                        }else if($processCategoryId === '3'){
                                            return false;
                                        }else if($processCategoryId === '5'){
                                            return false;
                                        }

                                        return true;

                                    })
                                    ->columnSpan(4)
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('ap')
                                    ->label('A.P')
                                    ->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return $processCategoryId !== '4';
                                    })
                                    ->columnSpan(4)
                                    ->maxLength(255),
                                    Forms\Components\TagsInput::make('subpoena')
                                    ->label('Comparendo')
                                    ->placeholder('Seleccione una etiqueta')
                                    ->columnSpan(12),
                                Forms\Components\TextInput::make('value_subpoema')
                                    ->label('Valor Comparendos')
                                    ->numeric()
                                    ->required()
                                    // ->disabled()
                                    ->columnSpan(3)
                                    ->dehydrated(),
                                Forms\Components\TextInput::make('total_value_paymet')
                                    ->label('Valor')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(3)
                                    ->dehydrated(),
                                Forms\Components\DatePicker::make('date_resolution')
                                    ->label('Fecha de Resolución')
                                    ->disabled(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return $processCategoryId !== '3';
                                    })
                                    ->columnSpan(3),
                                Forms\Components\Select::make('status_subpoema')
                                    ->label('Estado del Proceso')
                                    ->options([
                                        'pendiente' => 'Pendinete',
                                        'en proceso' => 'En Proceso',
                                        'finalizado' => 'Finalizado',
                                        'devuelto' => 'Devuelto'
                                    ])
                                    ->default('pendiente')
                                    ->required()
                                    ->columnSpan(3)
                                    ->searchable(),
                            ])->columns(12)
                            ->addActionLabel('Agregar Proceso')
                    ]),
                    Forms\Components\Section::make('Total')
                        ->columns(1)
                        ->schema([
                            Forms\Components\Placeholder::make('grand_value_placeholder')
                                ->label('Valor Total')
                                ->content(function (Get $get, Set $set){
                                    $total = 0;
                                    if(!$repeaters = $get('items')) {
                                        return $total;
                                    }

                                    foreach($repeaters as $key => $repeater){
                                        $total += $get("items.{$key}.total_value_paymet");
                                    }
                                    $set('grand_value', $total);
                                    return Number::currency($total, 'USD');
                                }),
                            Forms\Components\Hidden::make('grand_value')
                                ->default(0)
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
                                ->live(),
                            Forms\Components\TextInput::make('value_commission')
                                ->prefix('$')
                                ->label('Comisión')
                                ->numeric()
                                ->maxLength(11),
                        ]),
                    Forms\Components\Section::make('Estado de cuenta')
                        ->columns(1)
                        ->schema([
                            Forms\Components\FileUpload::make('status_account')
                                ->label('Estado de Cuenta')
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->preserveFilenames()
                                ->downloadable()
                                ->previewable(false)
                                ->uploadingMessage('Cargando Archivo...')
                                ->maxSize(2048),
                        ]),
                    Forms\Components\Section::make('Tramite del Comparendo')->schema([
                        Forms\Components\ToggleButtons::make('status')
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
                            ->maxLength(255)
                            ->columnSpan('full'),
                        Forms\Components\Toggle::make('paid')
                            ->label('Pagado')
                            ->inline(false)
                    ])->columns(1),
                    Forms\Components\Hidden::make('responsible_id')
                            ->default(fn () => Auth::id()),
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.dni')
                    ->label('Cedula')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grand_value')
                    ->label('Valor Total')
                    ->numeric()
                    ->sortable()
                    ->money('USD')
                    ->searchable(),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'Pendiente' => 'Pendiente',
                        'En Proceso' => 'En Proceso',
                        'Finalizado' => 'Finalizado',
                        'Devuelto' => 'Devuelto'
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('responsible.name')
                    ->label('Responsable')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
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
                        ExcelExport::make()->fromTable()->only(['user.name','user.dni','grand_value','status']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentProcessRelationManager::class,
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
            'index' => Pages\ListRevocations::route('/'),
            'create' => Pages\CreateRevocation::route('/create'),
            'view' => Pages\ViewRevocation::route('/{record}'),
            'edit' => Pages\EditRevocation::route('/{record}/edit'),
        ];
    }
}
