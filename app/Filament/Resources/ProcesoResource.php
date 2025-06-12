<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProcesoResource\Pages;
use App\Filament\Resources\ProcesoResource\RelationManagers;
use App\Models\Proceso;
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
use App\Models\ComisionProcesos;
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
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\ActionGroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProcesoMail;
use App\Filament\Resources\Storage;

class ProcesoResource extends Resource
{
    protected static ?string $model = Proceso::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

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

        function calculateTotalCategory(Set $set, Get $get) {
            $categories = $get('categoria_licencias');
            $isRenovation = $get('processcategory_id') == '10';

            // Solo considerar valor_carta_escuela si no es renovación
            $valorCartaEscuela = $isRenovation ? 0 : ($get('valor_carta_escuela') ?? 0);
            $valorExamen = ($get('examen_medico') === 'No aplica') ? 0 : ($get('valor_examen') ?? 0);
            $valorImpresion = ($get('impresion') === 'No aplica') ? 0 : ($get('valor_impresion') ?? 0);
            $valorSinCurso = ($get('sin_curso') === 'No aplica') ? 0 : ($get('valor_sin_curso') ?? 0);

            $total = $valorCartaEscuela + $valorExamen + $valorImpresion + $valorSinCurso;

            if ($isRenovation) {
                // Calcular valor de renovación según tipo y gestión
                $tipoRenovacion = $get('tipo_renovacion');
                $isCliente = $get('../../gestion') === 'Cliente';

                foreach ($categories as $categoryId) {
                    $category = LicensesSetupCategory::where('name', $categoryId)->first();

                    if ($tipoRenovacion == 'solo_examen') {
                        $price = $isCliente ?
                            ($category->price_renewal_exam_client ?? 0) :
                            ($category->price_renewal_exam_processor ?? 0);
                    } else {
                        $price = $isCliente ?
                            ($category->price_renewal_exam_slide_client ?? 0) :
                            ($category->price_renewal_exam_slide_processor ?? 0);
                    }

                    $total += $price;
                    $set('valor_renovacion', $price); // Mostrar el valor unitario
                }
            } else {
                // Cálculo normal para licencias nuevas
                if (is_array($categories)) {
                    foreach ($categories as $categoryId) {
                        $price = LicensesSetupCategory::where('name', $categoryId)->value('price') ?? 0;
                        $total += $price;
                    }
                }
            }

            $set('value_enlistment', $total);
            $set('total_value_paymet', $total);
        }

        function getDni(Set $set, Get $get) {
            $userId = $get('user_id');
            $user = User::find($userId);
            $dni = $user->dni ?? 'N/A';
            $set('dni', $dni);
        }

        function handleGestion(Set $set, Get $get) {
            $gestion = $get('gestion');
            $userId = $get('user_id');

            if ($gestion === 'Cliente') {
                getDni($set, $get);
                $set('dni_disabled', true);
            } else {
                $set('dni_disabled', false);
            }
        }

        function calculateCommissionPruea(Set $set, Get $get) {
            $userId = $get('../../user_id');
            $processor = User::find($userId);

            if (!$processor) {
                $set('total_value_paymet', 10);
                return;
            }

            $processCategoryId = $get('processcategory_id');
            $valorComparendo = $get('valor_comparendo') ?? 0;
            $porcentajeDescuento = $get('porcentaje_descuento') ?? 0;

            // Aplicar descuento al valor del comparendo
            $valorConDescuento = $valorComparendo;
            if ($porcentajeDescuento > 0) {
                $descuento = $valorComparendo * ($porcentajeDescuento / 100);
                $valorConDescuento = $valorComparendo - $descuento;
            }

            if ($get('../../gestion') === 'Tramitador') {
                switch ($processCategoryId) {
                    case 1: // cobro_coactivo
                    case 2: // adedudo
                    case 4: // acuedo_pago
                    case 5: // prescripcion
                        $categoria = match($processCategoryId) {
                            1 => 'cobro_coactivo',
                            2 => 'adedudo',
                            4 => 'acuedo_pago',
                            5 => 'prescripcion',
                            default => ''
                        };

                        $commission = ComisionProcesos::where('user_id', $processor->id)->value($categoria) ?? 0;
                        $commissionTotal = $valorConDescuento * ($commission / 100);
                        $set('total_value_paymet', $commissionTotal);
                        break;
                    default:
                        $set('total_value_paymet', $valorConDescuento);
                        break;
                }
            } else {
                $set('total_value_paymet', $valorConDescuento);
            }
        }

        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Informacion del Gestion')->schema([
                        Forms\Components\Select::make('gestion')
                            ->label('Gestion')
                            ->placeholder('Seleccione una enrrolamiento')
                            ->columnSpan(4)
                            ->options([
                                'Cliente' => 'Cliente',
                                'Tramitador' => 'Tramitador',
                            ])
                            ->live(),
                    ]),
                    Forms\Components\Section::make('Informacion del Cliente')->schema([
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
                            $set('dni', $user->dni ?? '');
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            $user = User::find($get('user_id'));
                            $set('name', $user->name ?? '');
                            $set('email', $user->email ?? '');
                            $set('phone', $user->phone ?? '');
                            $set('dni', $user->dni ?? '');
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
                                ->email()
                                ->unique(User::class, 'dni', ignoreRecord: true)
                                ->disabledOn('edit')
                                ->maxLength(255),
                            Forms\Components\TextInput::make('phone')
                                ->label('Teléfono')
                                ->numeric()
                                ->maxLength(11),
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
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->live()
                            ->disabled()
                            ->numeric()
                            ->maxLength(11),
                        Forms\Components\TextInput::make('dni')
                            ->label('Cedula')
                            ->live()
                            ->hidden()
                            ->required()
                            ->numeric(),
                    ])
                    ->hidden(function (Get $get) {
                        $gestion= $get('gestion');
                        return $gestion !== 'Cliente';
                    })
                    ->columns(3),
                    Forms\Components\Section::make('Informacion del Tramitador')->schema([
                        Forms\Components\Select::make('user_id')
                        ->label('Tramitador')
                        ->placeholder('Seleccione un documento')
                        ->relationship('client', 'name', function ($query) {
                            $query->whereHas('roles', function ($roleQuery) {
                                $roleQuery->where('name', 'tramitador');
                            });
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan('full')
                        ->live()
                    ])
                    ->hidden(function (Get $get) {
                        $gestion= $get('gestion');
                        return $gestion !== 'Tramitador';
                    })
                    ->columns(3),
                    Forms\Components\Section::make('Procesos')->schema([
                        Forms\Components\Repeater::make('proceso')
                            ->relationship('proceso')
                            ->schema([
                                Forms\Components\Select::make('processcategory_id')
                                    ->label('Proceseso')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(12)
                                    ->relationship('processcategory', 'name')
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $dni = $get('../../dni');
                                        $set('dni', $dni);
                                    }),
                                Forms\Components\TextInput::make('dni')
                                    ->label('Celuda')
                                    ->live()
                                    ->disabled(function (Get $get) {
                                        $gestion = $get('../../gestion');
                                        return $gestion !== 'Tramitador';
                                    })
                                    ->dehydrated(true)
                                    ->maxLength(11)
                                    ->columnSpan(12),
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
                                            //->reactive()
                                            ->live(onBlur: true)
                                            ->dehydrated()
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                calculateCommissionPruea($set, $get);
                                            })
                                            ->required()
                                            ->columnSpan(6),
                                        Forms\Components\TextInput::make('porcentaje_descuento')
                                            ->label('Porcentaje Descuento (%)')
                                            ->suffix('%')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->live(onBlur: true)
                                            ->hidden(function (Get $get) {
                                                    // Solo mostrar para: cobro coactivo (1), adeudo (2), acuerdo de pago (4), prescripción (5)
                                                    $visibleCategories = ['1', '2', '4', '5'];
                                                    return !in_array($get('processcategory_id'), $visibleCategories);
                                                })
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                $valorComparendo = $get('valor_comparendo');
                                                $porcentaje = $get('porcentaje_descuento');

                                                if ($valorComparendo && $porcentaje) {
                                                    $descuento = $valorComparendo * ($porcentaje / 100);
                                                    $valorFinal = $valorComparendo - $descuento;
                                                    $set('total_value_paymet', $valorFinal);
                                                }
                                            })
                                            ->columnSpan(6),
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
                                            ->options(function (Get $get) {
                                                $isRenovation = $get('processcategory_id') == '10';
                                                return LicensesSetupCategory::where('type', $isRenovation ? 'renovation' : 'normal')
                                                    ->pluck('name', 'name')
                                                    ->toArray();
                                            })
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

                                        // Nueva sección para renovaciones
                                        Forms\Components\Section::make('Opciones de Renovación')
                                            ->columns(12)
                                            ->schema([
                                                Forms\Components\Select::make('tipo_renovacion')
                                                    ->label('Tipo de Renovación')
                                                    ->options([
                                                        'solo_examen' => 'Solo examen',
                                                        'examen_lamina' => 'Examen y lámina'
                                                    ])
                                                    ->live()
                                                    ->required()
                                                    ->columnSpan(6)
                                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                                        calculateTotalCategory($set, $get);
                                                    })
                                                    ->hidden(fn (Get $get) => $get('processcategory_id') != '10'),

                                                Forms\Components\TextInput::make('valor_renovacion')
                                                    ->label('Valor Renovación')
                                                    ->prefix('$')
                                                    ->numeric()
                                                    ->columnSpan(6)
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->required()
                                                    ->hidden(fn (Get $get) => $get('processcategory_id') != '10'),
                                            ])
                                            ->hidden(fn (Get $get) => $get('processcategory_id') != '10'),

                                        // Campos ocultos para renovaciones
                                        Forms\Components\Select::make('escula')
                                            ->label('Escuela')
                                            ->placeholder('Seleccione una escuela')
                                            ->options(SchoolSetup::pluck('name_school','id')->toArray())
                                            ->columnSpan(4)
                                            ->hidden(fn (Get $get) => $get('processcategory_id') == '10'),

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
                                            ->hidden(fn (Get $get) => $get('processcategory_id') == '10'),

                                        Forms\Components\TextInput::make('valor_carta_escuela')
                                            ->label('Valor Carta Escuela')
                                            ->columnSpan(4)
                                            ->live()
                                            ->disabled()
                                            ->dehydrated()
                                            ->required()
                                            ->numeric()
                                            ->hidden(fn (Get $get) => $get('processcategory_id') == '10'),

                                        Forms\Components\Select::make('pin')
                                            ->label('Pines')
                                            ->columnSpan(12)
                                            ->placeholder('Seleccione una escuela')
                                            ->options(PinsProcess::pluck('name','id')->toArray())
                                            ->hidden(fn (Get $get) => $get('processcategory_id') == '10'),
                                    ])
                                    ->hidden(function (Get $get) {
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
                                                'No aplica' => 'No aplica',
                                                'Pendiente' => 'Pendiente',
                                                'Finalizado' => 'Finalizado',
                                                'Devuelto' => 'Devuelto'
                                            ])
                                            ->columnSpan(6)
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state === 'No aplica') {
                                                    $set('valor_examen', 0);
                                                }
                                            }),
                                        Forms\Components\Select::make('impresion')
                                            ->label('Impresión')
                                            ->placeholder('Seleccione un estado')
                                            ->options([
                                                'No aplica' => 'No aplica',
                                                'Pendiente' => 'Pendiente',
                                                'Finalizado' => 'Finalizado',
                                                'Devuelto' => 'Devuelto'
                                            ])
                                            ->columnSpan(6)
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state === 'No aplica') {
                                                    $set('valor_impresion', 0);
                                                }
                                            }),
                                        Forms\Components\TextInput::make('valor_examen')
                                            ->label('Valor exámenes')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->maxLength(11)
                                            ->disabled(function (Get $get) {
                                                return $get('examen_medico') === 'No aplica';
                                            })
                                            ->live(),
                                        Forms\Components\TextInput::make('valor_impresion')
                                            ->label('Valor impresión')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->maxLength(11)
                                            ->disabled(function (Get $get) {
                                                return $get('impresion') === 'No aplica';
                                            })
                                            ->live(),
                                        Forms\Components\Select::make('sin_curso')  // <-- Esta es la línea corregida
                                            ->label('Sin Curso')
                                            ->placeholder('Seleccione una opción')
                                            ->options([
                                                'No aplica' => 'No aplica',
                                                'Aplica' => 'Aplica'
                                            ])
                                            ->columnSpan(6)
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                if ($state === 'No aplica') {
                                                    $set('valor_sin_curso', 0);
                                                }
                                            }),
                                        Forms\Components\TextInput::make('valor_sin_curso')
                                            ->label('Valor Sin Curso')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->maxLength(11)
                                            ->disabled(function (Get $get) {
                                                return $get('sin_curso') === 'No aplica';
                                            })
                                            ->live(),
                                    ])->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return !in_array($processCategoryId, ['9']);
                                    }),
                                Forms\Components\Section::make('Información de la Controversia')
                                    ->columns(12)
                                    ->schema([
                                        Forms\Components\DateTimePicker::make('cita')
                                            ->label('Cita')
                                            ->columnSpan(4),
                                        Forms\Components\TextInput::make('codigo')
                                            ->label('Código')
                                            ->columnSpan(4)
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('ventana')
                                            ->label('Ventana')
                                            ->columnSpan(4)
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
                                    Forms\Components\TextInput::make('total_value_paymet')
                                        ->label('Valor')
                                        ->live(onBlur: true)
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
                            ])
                            ->columns(12)
                            ->columnSpan(12)
                            ->addActionLabel('Agregar Proceso'),

                            Forms\Components\Placeholder::make('grand_value_placeholder')
                                ->label('Valor Total')
                                ->content(function (Get $get, Set $set){
                                    $total = 0;
                                    if(!$repeaters = $get('proceso')) {
                                        return $total;
                                    }

                                    foreach($repeaters as $key => $repeater){
                                        $total += $get("proceso.{$key}.total_value_paymet");
                                    }

                                    $set('gran_total', $total);

                                    return Number::currency($total, 'USD');
                                }),
                            Forms\Components\Hidden::make('gran_total')
                                ->dehydrated()
                                ->live()
                                ->default(0),
                        ])->columns(2),
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
                            Forms\Components\TextInput::make('valor_comision')
                                ->prefix('$')
                                ->label('Comisión')
                                ->numeric()
                                ->maxLength(11),
                        ]),
                    Forms\Components\Section::make('Estado de cuenta')
                        ->columns(1)
                        ->schema([
                            Forms\Components\FileUpload::make('estado_cuenta')
                                ->label('Estado de Cuenta')
                                ->acceptedFileTypes(['image/*', 'application/pdf'])
                                ->preserveFilenames()
                                ->downloadable()
                                ->openable()
                                ->uploadingMessage('Cargando Archivo...')
                                ->maxSize(2048),
                        ]),
                    Forms\Components\Section::make('Tramite del Comparendo')->schema([
                        Forms\Components\ToggleButtons::make('estado')
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
                        Forms\Components\Textarea::make('observacion')
                            ->label('Observaciones')
                            ->live()
                            ->maxLength(255)
                            ->columnSpan('full'),
                        Forms\Components\Toggle::make('pagado')
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
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.dni')
                    ->label('Cedula')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_cuenta')
                    ->label('Estado de cuenta')
                    ->url(function ($state) {
                        if ($state) {
                            return route('estado.de.cuenta.download', ['filename' => $state]);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('proceso.porcentaje_descuento')
                    ->label('Descuento')
                    ->suffix('%')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('estado')
                    ->label('Estado de Proceso')
                    ->inline()
                    ->default('pendiente')
                    ->options([
                        'Pendiente' => 'Pendinete',
                        'En Proceso' => 'En Proceso',
                        'Finalizado' => 'Finalizado',
                        'Devuelto' => 'Devuelto'
                    ]),
                Tables\Columns\TextColumn::make('gran_total')
                    ->label('Total')
                    ->money('USD')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    Tables\Actions\Action::make('descargarPDF')
                        ->label('Factura')
                        ->action(function ($record) {
                            return redirect()->route('procesos.pdf', $record->id);
                        }),
                    Tables\Actions\Action::make('whatsapp')
                        ->label('Enviar PDF por WhatsApp')
                        ->color('success')
                        ->url(function ($record) {
                            $phone = $record->client->phone;
                            if (!$phone) {
                                return response()->json(['message' => 'El cliente no tiene número de teléfono.'], 400);
                            }

                            $pdfUrl = route('procesos.pdf', $record->id);

                            $whatsappUrl = "https://api.whatsapp.com/send?phone=57$phone&text=Hola%2C%20aquí%20tienes%20el%20PDF%20solicitado:%20$pdfUrl";

                            //return redirect()->away($whatsappUrl);
                            return $whatsappUrl;
                        })
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('email')
                        ->label('Enviar Email')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $email = $record->client->email;
                            if (!$email) {
                                return response()->json(['message' => 'El cliente no tiene correo electrónico.'], 400);
                            }

                            $client = $record->client;
                            $processor = $record->processor;
                            $registrarProcesos = $record->registrarProcesos;

                            $registrarProcesos = $record->registrarProcesos->map(function ($registrarProceso) {
                                return [
                                    'id' => $registrarProceso->id,
                                    'category_name' => $registrarProceso->processCategory ? $registrarProceso->processCategory->name : 'N/A',
                                ];
                            });

                            $pdfUrl = route('procesos.pdf', $record->id);

                            $dataToSend = [
                                'client_name' => $client->name,
                                'client_dni' => $client->dni,
                                'client_email' => $client->email,
                                'client_phone' => $client->phone,
                                'invoice' => $client->dni . '-' . $client->id . 'CTA',
                                'processor_name' => $processor ? $processor->name : '',
                                'total_value' => $record->gran_total,
                                'observations' => $record->observacion,
                                'created_at' => $record->created_at,
                                'registrar_procesos' => $registrarProcesos,
                                'pdf_url' => $pdfUrl,
                            ];

                            Mail::to($client)->send(new ProcesoMail($dataToSend));

                            return response()->json(['message' => 'Correo enviado exitosamente.'], 200);
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProcesos::route('/'),
            'create' => Pages\CreateProceso::route('/create'),
            'edit' => Pages\EditProceso::route('/{record}/edit'),
        ];
    }
}
