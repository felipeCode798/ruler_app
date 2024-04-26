<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PinResource\Pages;
use App\Filament\Resources\PinResource\RelationManagers;
use App\Models\Pins;
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
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\ActionGroup;

class PinResource extends Resource
{
    protected static ?string $model = Pins::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Gestión de Licencias';

    public static function form(Form $form): Form
    {
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
                    ->options([
                        'a2' => 'A2',
                        'b1' => 'B1',
                        'c1' => 'C1',
                        'c2' => 'C2',
                    ])
                    ->required()
                    ->columns(4)
                    ->gridDirection('row'),
                ]),
                Forms\Components\Section::make('Enrronlamiento')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('enlistment')
                        ->label('Enrrolamiento')
                        ->placeholder('Seleccione un enrrolamiento')
                        ->options([
                            'cruce pin' => 'Cruce Pin',
                            'guardado' => 'Guardado',
                        ])
                    ->required(),
                    Forms\Components\TextInput::make('certificate')
                        ->label('Certificado')
                        ->required()
                        ->maxLength(255),
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
                        ->required(),
                    Forms\Components\TextInput::make('value_commission')
                        ->prefix('$')
                        ->label('Comisión')
                        ->required()
                        ->numeric()
                        ->maxLength(11),
                ]),
                Forms\Components\Section::make('Tramite del Pin')
                ->columns(2)
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
                    Forms\Components\TextInput::make('total_value')
                        ->label('Valor total')
                        ->required()
                        ->numeric()
                        ->maxLength(11),
                    Forms\Components\Textarea::make('observations')
                        ->label('Observaciones')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan('full'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.dni')
                    ->label('Nombre')
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
                Tables\Columns\TextColumn::make('certificate')
                    ->label('Certificado')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor total')
                    ->searchable()
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum')
                    ->label('Pagos')
                    ->money('USD')
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                ActionGroup::make([
                    Tables\Actions\Action::make('Iniciar Proceso', 'start_process')
                        ->action(function (Pins $Pins) {
                            ProcessReturn::create([
                                'user_id' => $Pins->user_id,
                                'type_process' => '	pins',
                                'process_id' => $Pins->id,
                            ]);

                            $Pins->update(['state' => 'return']);

                            return redirect()->back();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['client.name','category','enlistment','certificate','state','observations','processor.name','value_commission','total_value','payments_sum']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PinspaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPins::route('/'),
            'create' => Pages\CreatePin::route('/create'),
            'edit' => Pages\EditPin::route('/{record}/edit'),
        ];
    }
}
