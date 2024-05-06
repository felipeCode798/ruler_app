<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ControversyResource\Pages;
use App\Filament\Resources\ControversyResource\RelationManagers;
use App\Models\Controversy;
use App\Models\User;
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
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\ActionGroup;
use App\Models\Category;


class ControversyResource extends Resource
{
    protected static ?string $model = Controversy::class;
    protected static ?string $navigationLabel = 'Controversias';
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    public static function form(Form $form): Form
    {

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
                $commission = $processor->processingCommissions->first()->prescriptions_commission ?? 0;
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
            $categoryId = $get('category_id');
            $category = Category::find($categoryId);

            $transito = $category->value_transport ?? 0;
            $cia = $category->value_cia_des ?? 0;

            $value = $transito + $cia;

            if ($category) {
                $set('value', $value);
                $set('value_received', $category->value_subpoena);
                calculateTotalValues($set, $get);
            } else {
                $set('value', 0);
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
                Forms\Components\Section::make('Información de la Controversia')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Categoría')
                        ->placeholder('Seleccione una categoría')
                        ->relationship('category', 'name')
                        ->required()
                        ->live()
                        ->columnSpan('full')
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            updateValue($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            updateValue($set, $get);
                        }),
                    Forms\Components\TagsInput::make('subpoena')
                        ->label('Comparendo')
                        ->placeholder('Seleccione una etiqueta')
                        ->columnSpan('full')
                        ->required(),
                    Forms\Components\TextInput::make('value_received')
                        ->label('Valor Comparendo')
                        ->prefix('$')
                        ->required()
                        ->live()
                        ->numeric()
                        ->disabled()
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
                        ->disabled()
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
                        ->maxLength(11)
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateTotalValues($set, $get);
                        }),
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
                        ->required()
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable(false)
                        ->uploadingMessage('Cargando Archivo...')
                        ->maxSize(2048),
                    Forms\Components\FileUpload::make('document_power')
                        ->label('Poder')
                        ->required()
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->preserveFilenames()
                        ->downloadable()
                        ->previewable(false)
                        ->uploadingMessage('Cargando Archivo...')
                        ->maxSize(2048),
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
                        ->relationship('processor', 'name', function ($query) {
                            $query->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'tramitador');
                            });
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        }),
                    Forms\Components\TextInput::make('value_commission')
                        ->prefix('$')
                        ->label('Comisión')
                        ->live()
                        ->disabled()
                        ->maxLength(255),
                ]),
                Forms\Components\Section::make('Tramite del Controversia')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('state')
                    ->label('Estado')
                    ->placeholder('Seleccione un estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_process' => 'En Proceso',
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
                    ->label('Valor Debito')
                    ->disabled()
                    ->live(),
                    Forms\Components\TextInput::make('total_gains')
                    ->prefix('$')
                    ->label('Valor Ganancias')
                    ->disabled()
                    ->live(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Controversy::query()->where('state', '!=', 'return'))
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
                    ->label('Pagado')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payments_sum_processor')
                    ->money('USD')
                    ->label('Comisión')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
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
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('Descargar', 'download')
                        ->requiresConfirmation()
                        ->action(function (Controversy $controversy) {
                            return redirect()->route('download', [
                                'filename' => $controversy->document_dni,
                                'filename2' => $controversy->document_power,
                            ]);
                        }),
                    Tables\Actions\Action::make('Devolucion', 'start_process')
                        ->requiresConfirmation()
                        ->action(function (Controversy $controversy) {
                            ProcessReturn::create([
                                'user_id' => $controversy->user_id,
                                'type_process' => 'controversies',
                                'process_id' => $controversy->id,
                            ]);

                            $controversy->update(['state' => 'return']);

                            return redirect()->back();
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['client.name', 'appointment', 'code', 'window', 'state', 'total_value']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ControversypaymentsRelationManager::class,
            RelationManagers\ProcessorcontroversypaymentsRelationManager::class,
            RelationManagers\SupplierControversyPaymentsRelationManager::class,
            //RelationManagers\PenaltycontroversypaymentsRelationManager::class,
        ];
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
