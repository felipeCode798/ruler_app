<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrarProcesoResource\Pages;
use App\Filament\Resources\RegistrarProcesoResource\RelationManagers;
use App\Models\RegistrarProceso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ControversyProcess;
use App\Models\CategoryRevocation;
use App\Models\LicensesSetupCategory;
use App\Models\SchoolSetup;
use App\Models\PinsProcess;
use App\Models\Proceso;
use App\Models\Lawyer;
use App\Models\Filter;
use Spatie\Permission\Models\Role;
use Filament\Forms\Set;
use Filament\Forms\Get;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Actions\ActionGroup;


class RegistrarProcesoResource extends Resource
{
    protected static ?string $model = RegistrarProceso::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function form(Form $form): Form
    {

        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function calculateTotalCategory(Set $set, Get $get) {
            $categories = $get('categoria_licencias');

            if (!is_array($categories)) {
                $categories = [];
            }

            $total = 0;

            foreach ($categories as $categoryId) {
                $price = LicensesSetupCategory::where('name', $categoryId)->value('price');
                $total += $price;
            }

            $set('value_enlistment', $total);
        }

        function calculateFilter(Set $set, Get $get) {
            $filter = Filter::find($get('filter_id'));

            if ($filter) {
                $commission = $filter->commission ?? 0;
                $pago = $get('valor_comparendo') * ($commission / 100);
                $pago = round($pago);
                $set('pago_filtro', $pago);
            } else {
                $set('pago_filtro', 0);
            }
        }

        function calculateLawyer(Set $set, Get $get) {
            $lawyer = Lawyer::find($get('lawyer_id'));

            if ($lawyer) {
                $commission = $lawyer->commission ?? 0;
                $pago = $get('valor_comparendo') * ($commission / 100);
                $pago = round($pago);
                $set('pago_abogado', $pago);
            } else {
                $set('pago_abogado', 0);
            }
        }

        return $form
            ->schema([
                Forms\Components\Section::make('Informacion del Proceso')->schema([
                    Forms\Components\Select::make('proceso_id')
                        ->label('Proceso')
                        ->relationship('proceso', 'id')
                        ->searchable()
                        ->hidden()
                        ->dehydrated()
                        ->preload()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $proceso = Proceso::find($get('proceso_id'));
                            if ($proceso && $proceso->user) {
                                $set('user_name', $proceso->user->name);
                                $set('user_email', $proceso->user->email);
                                $set('user_phone', $proceso->user->phone);
                                $set('user_dni', $proceso->user->dni);
                            }
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            $proceso = Proceso::find($get('proceso_id'));
                            if ($proceso && $proceso->user) {
                                $set('user_name', $proceso->user->name);
                                $set('user_email', $proceso->user->email);
                                $set('user_phone', $proceso->user->phone);
                                $set('user_dni', $proceso->user->dni);
                            }
                        })
                        ->required(),
                    Forms\Components\TextInput::make('user_name')
                        ->label('Nombre')
                        ->disabled(),
                    Forms\Components\TextInput::make('user_dni')
                        ->label('Cedula')
                        ->disabled(),
                    Forms\Components\TextInput::make('user_email')
                        ->label('Email')
                        ->disabled(),
                    Forms\Components\TextInput::make('user_phone')
                        ->label('Teléfono')
                        ->disabled(),
                ])->columns(4),
                Forms\Components\Section::make('Proceso')->schema([
                    Forms\Components\Select::make('processcategory_id')
                        ->label('Categoria')
                        ->relationship('processcategory', 'name')
                        ->columnSpan(12)
                        ->default(null),
                    Forms\Components\TextInput::make('simit')
                        ->prefix('$')
                        ->label('Simit')
                        ->columnSpan(12)
                        ->numeric()
                        ->maxLength(11),
                    Forms\Components\Select::make('categoryrevocation_id')
                        ->label('Categoria')
                        ->searchable()
                        ->preload()
                        ->columnSpan(12)
                        ->hidden(function (Get $get) {
                            $visibleCategories = ['3', '7', '8'];
                            return !in_array($get('processcategory_id'), $visibleCategories);
                        })
                        ->relationship('categoryrevocation', 'name'),
                    Forms\Components\Select::make('lawyer_id')
                        ->label('Abogado')
                        ->searchable()
                        ->preload()
                        ->columnSpan(6)
                        ->hidden(function (Get $get) {
                            $visibleCategories = ['1', '2', '3', '4', '5', '6', '7'];
                            return !in_array($get('processcategory_id'), $visibleCategories);
                        })
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateLawyer($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateLawyer($set, $get);
                        })
                        ->reactive()
                        ->relationship('lawyer', 'name'),
                    Forms\Components\TextInput::make('pago_abogado')
                        ->prefix('$')
                        ->label('Pago Abogado')
                        ->columnSpan(6)
                        ->numeric()
                        ->hidden(function (Get $get) {
                            $visibleCategories = ['1', '2', '3', '4', '5', '6', '7'];
                            return !in_array($get('processcategory_id'), $visibleCategories);
                        })
                        ->maxLength(11),
                    Forms\Components\Select::make('filter_id')
                        ->label('Filtro')
                        ->searchable()
                        ->preload()
                        ->columnSpan(6)
                        ->hidden(function (Get $get) {
                            $visibleCategories = ['1', '2', '3', '4', '5', '6', '7'];
                            return !in_array($get('processcategory_id'), $visibleCategories);
                        })
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateFilter($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateFilter($set, $get);
                        })
                        ->reactive()
                        ->relationship('filter', 'name'),
                    Forms\Components\TextInput::make('pago_filtro')
                        ->prefix('$')
                        ->label('Pago Filtro')
                        ->columnSpan(6)
                        ->numeric()
                        ->hidden(function (Get $get) {
                            $visibleCategories = ['1', '2', '3', '4', '5', '6', '7'];
                            return !in_array($get('processcategory_id'), $visibleCategories);
                        })
                        ->maxLength(11),
                    Forms\Components\TextInput::make('sa')
                        ->label('S.A')
                        ->hidden(function (Get $get) {
                            $visibleCategories = ['2', '3', '5'];
                            return !in_array($get('processcategory_id'), $visibleCategories);
                        })
                        ->columnSpan(12)
                        ->maxLength(255),
                    Forms\Components\TextInput::make('ap')
                        ->label('A.P')
                        ->hidden(function (Get $get) {
                            $processCategoryId = $get('processcategory_id');
                            return $processCategoryId !== '4';
                        })
                        ->columnSpan(12)
                        ->maxLength(255),
                ])->columns(12),

                Forms\Components\Section::make('Información del Comparendos')
                    ->columns(12)
                    ->schema([
                        Forms\Components\TagsInput::make('comparendo')
                            ->label('Comparendo')
                            ->placeholder('Seleccione una etiqueta')
                            ->columnSpan(12),
                        Forms\Components\TextInput::make('valor_comparendo')
                            ->label('Valor Comparendos')
                            ->numeric()
                            ->required()
                            ->columnSpan(12)
                            ->dehydrated(),
                        Forms\Components\DatePicker::make('date_resolution')
                            ->label('Fecha de Resolución')
                            ->hidden(function (Get $get) {
                                $processCategoryId = $get('processcategory_id');
                                return $processCategoryId !== '3';
                            })
                            ->columnSpan(12),
                    ])->hidden(function (Get $get) {
                        $visibleCategories = ['1', '2', '3', '4', '5', '6', '7','8'];
                        return !in_array($get('processcategory_id'), $visibleCategories);
                    }),
                Forms\Components\Section::make('Valores de Tramite')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('valor_cia')
                            ->prefix('$')
                            ->label('Valor CIA')
                            ->required()
                            ->numeric()
                            ->live(),
                        Forms\Components\TextInput::make('valor_transito')
                            ->prefix('$')
                            ->label('Valor Tránsito')
                            ->required()
                            ->numeric()
                            ->live(),
                    ])->hidden(function (Get $get) {
                        $processCategoryId = $get('processcategory_id');
                        return $processCategoryId !== '8';
                    }),
                Forms\Components\Section::make('Información del Licencia')
                    ->columns(12)
                    ->schema([
                        Forms\Components\CheckboxList::make('categoria_licencias')
                            ->label('Categoría')
                            ->options(LicensesSetupCategory::pluck('name', 'name')->toArray())
                            ->required()
                            ->columns(4)
                            ->columnSpan(12)
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                calculateTotalCategory($set, $get);
                            })
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                calculateTotalCategory($set, $get);
                            })
                            ->gridDirection('row'),
                        Forms\Components\Select::make('escula')
                            ->label('Escuela')
                            ->placeholder('Seleccione una escuela')
                            ->options(SchoolSetup::pluck('name_school','id')->toArray())
                            ->columnSpan(4)
                            ->required(),
                        Forms\Components\Select::make('enrrolamiento')
                            ->label('Enrrolamiento')
                            ->placeholder('Seleccione una enrrolamiento')
                            ->columnSpan(4)
                            ->options([
                                'Cruce Pin' => 'Cruce Pin',
                                'Guardado' => 'Guardado',
                                'Abono' => 'Abono',
                                'Pagado' => 'Pagado',
                            ])
                            ->live()
                            ->required(),
                        Forms\Components\TextInput::make('valor_carta_escuela')
                            ->label('Valor Carta Escuela')
                            ->columnSpan(4)
                            ->live()
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->numeric(),
                        Forms\Components\Select::make('pin')
                            ->label('Pines')
                            ->columnSpan(12)
                            ->placeholder('Seleccione una escuela')
                            ->options(PinsProcess::pluck('name','id')->toArray()),
                    ])->hidden(function (Get $get) {
                        $visibleCategories = ['9', '10'];
                        return !in_array($get('processcategory_id'), $visibleCategories);
                    }),
                Forms\Components\Section::make('Información del Licencia')
                    ->columns(12)
                    ->schema([
                        Forms\Components\Select::make('examen_medico')
                            ->label('Exámenes médicos')
                            ->placeholder('Seleccione un estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Finalizado' => 'Finalizado',
                                'Devuelto' => 'Devuelto'
                            ])
                            ->columnSpan(6)
                            ->required(),
                        Forms\Components\Select::make('impresion')
                            ->label('Impresión')
                            ->placeholder('Seleccione un estado')
                            ->options([
                                'Pendiente' => 'Pendiente',
                                'Finalizado' => 'Finalizado',
                                'Devuelto' => 'Devuelto'
                            ])
                            ->columnSpan(6)
                            ->required(),
                        Forms\Components\TextInput::make('valor_examen')
                            ->label('Valor exámenes')
                            ->prefix('$')
                            ->required()
                            ->numeric()
                            ->columnSpan(6)
                            ->maxLength(11)
                            ->live(),
                        Forms\Components\TextInput::make('valor_impresion')
                            ->label('Valor impresión')
                            ->prefix('$')
                            ->required()
                            ->numeric()
                            ->columnSpan(6)
                            ->maxLength(11)
                            ->live(),
                    ])->hidden(function (Get $get) {
                        $visibleCategories = ['9', '10'];
                        return !in_array($get('processcategory_id'), $visibleCategories);
                    }),
                Forms\Components\Section::make('Información de la Controversia')
                    ->columns(12)
                    ->schema([
                        Forms\Components\DateTimePicker::make('cita')
                            ->label('Cita')
                            ->columnSpan(4)
                            ->required(),
                        Forms\Components\TextInput::make('codigo')
                            ->label('Código')
                            ->columnSpan(4)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('ventana')
                            ->label('Ventana')
                            ->columnSpan(4)
                            ->required()
                            ->maxLength(255),
                    ])->hidden(function (Get $get) {
                        $processCategoryId = $get('processcategory_id');
                        return $processCategoryId !== '7';
                    }),
                Forms\Components\Section::make('Documentacion de la Controversia')
                    ->columns(12)
                    ->schema([
                        Forms\Components\FileUpload::make('documento_dni')
                            ->label('Documento de Identidad')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->preserveFilenames()
                            ->columnSpan(6)
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Cargando Archivo...')
                            ->maxSize(2048),
                        Forms\Components\FileUpload::make('documento_poder')
                            ->label('Poder')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->preserveFilenames()
                            ->columnSpan(6)
                            ->downloadable()
                            ->previewable(false)
                            ->uploadingMessage('Cargando Archivo...')
                            ->maxSize(2048),
                    ])->hidden(function (Get $get) {
                        $processCategoryId = $get('processcategory_id');
                        return $processCategoryId !== '7';
                    }),
                Forms\Components\Section::make('Informacion del Proceso')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('total_value_paymet')
                            ->label('Valor')
                            ->numeric()
                            ->required()
                            ->columnSpan(6)
                            ->dehydrated(),
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
                            ->columnSpan(6)
                            ->searchable(),
                            ]),
                Forms\Components\Section::make('Información del Tramitador')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('processor_id')
                            ->label('Tramitador')
                            ->placeholder('Seleccione un tramitador')
                            ->relationship('proceso', 'id')
                            ->searchable()
                            ->hidden()
                            ->afterStateUpdated(function (Set $set, Get $get) {
                                $proceso = Proceso::find($get('proceso_id'));
                                if ($proceso && $proceso->user) {
                                    $set('user_tramidator', $proceso->processor->name);
                                    $set('comision', $proceso->valor_comision);
                                }
                            })
                            ->afterStateHydrated(function (Set $set, Get $get) {
                                $proceso = Proceso::find($get('proceso_id'));
                                if ($proceso && $proceso->user) {
                                    $set('user_tramidator', $proceso->processor->name);
                                    $set('comision', $proceso->valor_comision);
                                }
                            })
                            ->preload(),
                        Forms\Components\TextInput::make('user_tramidator')
                            ->label('Tramitador')
                            ->disabled(),
                        Forms\Components\TextInput::make('comision')
                            ->prefix('$')
                            ->label('Comisión')
                            ->disabled()
                            ->numeric()
                            ->maxLength(11),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('proceso.gestion')
                    ->label('Gestion')
                    ->sortable(),
                Tables\Columns\TextColumn::make('proceso.user.name')
                    ->label('Nombre')
                    ->sortable(),
                Tables\Columns\TextColumn::make('proceso.user.dni')
                    ->label('Cedula')
                    ->copyable()
                    ->copyMessage('Cedula Copiada')
                    ->sortable(),
                Tables\Columns\TextColumn::make('processcategory.name')
                    ->label('Proceso')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('categoryrevocation.name')
                    ->label('Categoria')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\SelectColumn::make('lawyer_id')
                    ->label('Abogado')
                    ->options(function () {
                        return Lawyer::all()->pluck('name', 'id');
                    })
                    ->sortable()
                    ->searchable()
                    ->updateStateUsing(function ($record, $state) {
                        $record->lawyer_id = $state;
                        $lawyer = Lawyer::find($state);
                        if ($lawyer) {
                            $commission = $lawyer->commission ?? 0;
                            $pago = $record->valor_comparendo * ($commission / 100);
                            $record->pago_abogado = round($pago);
                        } else {
                            $record->pago_abogado = 0;
                        }
                        $record->save();
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('pago_abogado')
                    ->label('Pago Abogado')
                    ->numeric()
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\SelectColumn::make('filter_id')
                    ->label('Filtro')
                    ->options(function () {
                        return Filter::all()->pluck('name', 'id');
                    })
                    ->sortable()
                    ->searchable()
                    ->updateStateUsing(function ($record, $state) {
                        $record->filter_id = $state;
                        $filter = Filter::find($state);
                        if ($filter) {
                            $commission = $filter->commission ?? 0;
                            $pago = $record->valor_comparendo * ($commission / 100);
                            $record->pago_filtro = round($pago);
                        } else {
                            $record->pago_filtro = 0;
                        }
                        $record->save();
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('pago_filtro')
                    ->label('Pago Filtro')
                    ->numeric()
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('simit')
                    ->label('Simit')
                    ->rules(['numeric'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('escula')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('enrrolamiento')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('valor_carta_escuela')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('pin')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('examen_medico')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('impresion')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('valor_examen')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('valor_impresion')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('valor_comparendo')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('valor_cia')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('valor_transito')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('codigo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ventana')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cita')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('date_resolution')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('documento_dni')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('documento_poder')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ap')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_value_paymet')
                    ->label('Valor')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status_subpoema')
                    ->label('Estado')
                    ->options([
                        'pendiente' => 'Pendinete',
                        'en proceso' => 'En Proceso',
                        'finalizado' => 'Finalizado',
                        'devuelto' => 'Devuelto'
                    ])
                    ->searchable(),
                Tables\Columns\IconColumn::make('pagado')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    Tables\Actions\Action::make('Descargar', 'download')
                        ->hidden(function (RegistrarProceso $registrarproceso) {
                            return $registrarproceso->processcategory_id != '7';
                        })
                        ->action(function (RegistrarProceso $registrarproceso) {
                            return redirect()->route('download', [
                                'filename' => $registrarproceso->documento_dni,
                                'filename2' => $registrarproceso->documento_poder,
                            ]);
                        }),
                ])
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
            RelationManagers\PagosRelationManager::class,
        ];
    }

    public static function getNavigationBadgeColor(): string|array|null {
        return static::getModel()::where('status_subpoema', 'pendiente')->count() > 10 ? 'success' : 'danger';
    }

    public static function getNavigationBadge(): ?string {
        return static::getModel()::where('status_subpoema', 'pendiente')->count();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrarProcesos::route('/'),
            'create' => Pages\CreateRegistrarProceso::route('/create'),
            'edit' => Pages\EditRegistrarProceso::route('/{record}/edit'),
        ];
    }
}
