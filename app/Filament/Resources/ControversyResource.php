<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControversyResource\Pages;
use App\Filament\Resources\ControversyResource\RelationManagers;
use App\Models\Controversy;
use App\Models\ControversyProcess;
use App\Models\CategoryRevocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class ControversyResource extends Resource
{
    protected static ?string $model = Controversy::class;

    protected static ?string $navigationLabel = 'Controversias';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Form $form): Form
    {
        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function updateValue(Set $set, Get $get) {
            $categoryId = $get('categoryrevocation_id');
            $category = CategoryRevocation::find($categoryId);

            $transito = $category->transit_value ?? 0;
            $cia = $category->cia_total_value ?? 0;

            $value = $transito + $cia;

            if ($category) {
                $set('value', $value);
                $set('value_received', $category->comparing_value);
            } else {
                $set('value', 0);
            }
        }

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
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('categoryrevocation_id')
                                    ->label('Categoria')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(3)
                                    ->reactive()
                                    ->relationship('categoryrevocation', 'name')
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        updateValue($set, $get);
                                    })
                                    ->afterStateHydrated(function (Set $set, Get $get) {
                                        updateValue($set, $get);
                                    }),
                                Forms\Components\TextInput::make('value_received')
                                    ->label('Valor Comparendo')
                                    ->prefix('$')
                                    ->required()
                                    ->columnSpan(3)
                                    ->live()
                                    ->dehydrated()
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('value')
                                    ->label('Valor')
                                    ->prefix('$')
                                    ->required()
                                    ->columnSpan(3)
                                    ->maxLength(255)
                                    ->disabled()
                                    ->dehydrated()
                                    ->live(),
                                Forms\Components\TextInput::make('total_value')
                                    ->label('Valor Recibido')
                                    ->prefix('$')
                                    ->required()
                                    ->columnSpan(3)
                                    ->numeric()
                                    ->maxLength(11)
                                    ->live(onBlur: true),
                                Forms\Components\TagsInput::make('subpoena')
                                    ->label('Comparendo')
                                    ->placeholder('Seleccione una etiqueta')
                                    ->columnSpan('full')
                                    ->required(),
                            ])->columns(12)
                            ->addActionLabel('Agregar Controversia')
                        ]),
                    Forms\Components\Section::make('Total')
                        ->columns(2)
                        ->schema([
                            Forms\Components\Placeholder::make('grand_value_placeholder')
                                ->label('Valor Total Comparendo')
                                ->content(function (Get $get, Set $set){
                                    $total = 0;
                                    if(!$repeaters = $get('items')) {
                                        return $total;
                                    }

                                    foreach($repeaters as $key => $repeater){
                                        $total += $get("items.{$key}.value_received");
                                    }
                                    return Number::currency($total, 'USD');
                                }),
                            Forms\Components\Placeholder::make('value_placeholder')
                                ->label('Valor Total A Pagar')
                                ->content(function (Get $get, Set $set){
                                    $total = 0;
                                    if(!$repeaters = $get('items')) {
                                        return $total;
                                    }

                                    foreach($repeaters as $key => $repeater){
                                        $total += $get("items.{$key}.total_value");
                                    }
                                    $set('grand_value', $total);
                                    return Number::currency($total, 'USD');
                                }),
                            Forms\Components\Hidden::make('grand_value')
                                ->default(0)
                        ]),
                    Forms\Components\Section::make('Información de la Controversia')
                        ->columns(3)
                        ->schema([
                            Forms\Components\DateTimePicker::make('appointment')
                                ->label('Cita')
                                ->required(),
                            Forms\Components\TextInput::make('code')
                                ->label('Código')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('window')
                                ->label('Ventana')
                                ->required()
                                ->maxLength(255),
                        ]),
                        Forms\Components\Section::make('Documentacion de la Controversia')
                        ->columns(3)
                        ->schema([
                            Forms\Components\FileUpload::make('document_dni')
                                ->label('Documento de Identidad')
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->preserveFilenames()
                                ->downloadable()
                                ->previewable(false)
                                ->uploadingMessage('Cargando Archivo...')
                                ->maxSize(2048),
                            Forms\Components\FileUpload::make('document_power')
                                ->label('Poder')
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->preserveFilenames()
                                ->downloadable()
                                ->previewable(false)
                                ->uploadingMessage('Cargando Archivo...')
                                ->maxSize(2048),
                            Forms\Components\FileUpload::make('status_account')
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
                                    ->live()
                                    // ->disabled()
                                    ->maxLength(255),
                            ]),
                            Forms\Components\Section::make('Tramite del Controversia')
                            ->columns(1)
                            ->schema([
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
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan('full'),
                                Forms\Components\Toggle::make('paid')
                                    ->label('Pagado')
                                    ->inline(false)
                            ]),
                            Forms\Components\Hidden::make('responsible_id')
                            ->default(fn () => Auth::id()),
                ])->columnSpanFull()
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
                Tables\Columns\TextColumn::make('appointment')
                    ->label('Cita')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('window')
                    ->label('Ventana')
                    ->searchable()
                    ->sortable(),
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
                Tables\Columns\TextColumn::make('grand_value')
                    ->money('USD')
                    ->label('Valor total')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('paid')
                    ->label('Pagado')
                    ->searchable()
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
                        ExcelExport::make()->fromTable()->only(['client.name','client.dni','appointment','code','window','status','grand_value','created_at']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentControversyRelationManager::class,
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
            'index' => Pages\ListControversies::route('/'),
            'create' => Pages\CreateControversy::route('/create'),
            'edit' => Pages\EditControversy::route('/{record}/edit'),
        ];
    }
}
