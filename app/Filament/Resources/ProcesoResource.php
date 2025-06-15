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
use App\Models\ControversyCategory;
use App\Models\LicensesSetupCategory;
use App\Models\SchoolSetup;
use App\Models\PinsProcess;
use App\Models\ComisionProcesos;
use App\Models\CourseCategory;
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

            // Obtener valores de cada componente
            $valorCartaEscuela = $isRenovation ? 0 : ($get('valor_carta_escuela') ?? 0);
            $valorExamen = ($get('examen_medico') === 'No aplica') ? 0 : ($get('valor_examen') ?? 0);
            $valorImpresion = ($get('impresion') === 'No aplica') ? 0 : ($get('valor_impresion') ?? 0);
            $valorSinCurso = ($get('sin_curso') === 'No aplica') ? 0 : ($get('valor_sin_curso') ?? 0);

            // Calcular total inicial
            $total = $valorCartaEscuela + $valorExamen + $valorImpresion + $valorSinCurso;

            if ($isRenovation) {
                // Lógica para renovaciones (igual que antes)
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
                    $set('valor_renovacion', $price);
                }
            } else {
                // Para licencias nuevas
                if (is_array($categories)) {
                    $totalExam = 0;
                    $totalSlide = 0;
                    $totalSchoolLetter = 0;
                    $totalNoCourse = 0;

                    foreach ($categories as $categoryId) {
                        $category = LicensesSetupCategory::where('name', $categoryId)->first();

                        // Sumar cada componente por separado
                        $totalExam += $category->price_exam ?? 0;
                        $totalSlide += $category->price_slide ?? 0;
                        $totalSchoolLetter += $category->school_letter ?? 0;
                        $totalNoCourse += $category->price_no_course ?? 0;
                    }

                    // Actualizar los valores individuales
                    $set('valor_carta_escuela', $totalSchoolLetter);

                    // Solo actualizar estos valores si no están ya establecidos por el estado
                    if ($get('examen_medico') !== 'No aplica' && $get('valor_examen') === null) {
                        $set('valor_examen', $totalExam);
                    }

                    if ($get('impresion') !== 'No aplica' && $get('valor_impresion') === null) {
                        $set('valor_impresion', $totalSlide);
                    }

                    if ($get('sin_curso') !== 'No aplica' && $get('valor_sin_curso') === null) {
                        $set('valor_sin_curso', $totalNoCourse);
                    }

                    $total += $totalExam + $totalSlide + $totalSchoolLetter + $totalNoCourse;
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
                return;
            }

            $processCategoryId = $get('processcategory_id');
            $valorComparendo = $get('valor_comparendo') ?? 0;
            $porcentajeDescuento = $get('porcentaje_descuento') ?? 0;

            if ($valorComparendo <= 0) {
                return;
            }

            // For comparendo processes (ID 6), use the calculated value
            if ($processCategoryId === '6') {
                $set('total_value_paymet', $get('valor') ?? 0);
                return;
            }

            // Rest of your existing logic...
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

        function actualizarValoresCurso(Set $set, Get $get, bool $state, string $descuentoTipo) {
            $cursoId = $get('coursecategory_id');
            $gestion = $get('../../gestion'); // Obtener si es Cliente o Tramitador

            // Si se desmarca un checkbox, limpiar los valores
            if (!$state) {
                $set('valor_transito', null);
                $set('valor', null);
                $set('total_value_paymet', null);

                // Desmarcar el otro checkbox si está marcado
                if ($descuentoTipo === '50') {
                    $set('descuento_25', false);
                } else {
                    $set('descuento_50', false);
                }
                return;
            }

            // Asegurarse que solo un checkbox esté marcado
            if ($descuentoTipo === '50') {
                $set('descuento_25', false);
            } else {
                $set('descuento_50', false);
            }

            // Obtener los datos del curso
            $curso = CourseCategory::find($cursoId);

            if (!$curso) {
                return;
            }

            // Establecer los valores según el tipo de descuento y gestión
            if ($descuentoTipo === '50') {
                $set('valor_transito', $curso->transit_value_50);

                if ($gestion === 'Cliente') {
                    $valor = $curso->client_value_50;
                } else {
                    $valor = $curso->processor_value_50;
                }
                $set('valor', $valor);
            } else {
                $set('valor_transito', $curso->transit_value_25);

                if ($gestion === 'Cliente') {
                    $valor = $curso->client_value_25;
                } else {
                    $valor = $curso->processor_value_25;
                }
                $set('valor', $valor);
            }

            // Actualizar el total_value_paymet con el mismo valor
            $set('total_value_paymet', $valor);
        }

       function actualizarValoresComparendo(Set $set, Get $get, bool $state, string $descuentoTipo) {
            $comparendoId = $get('categoryrevocation_id');
            $gestion = $get('../../gestion'); // Get if it's Cliente or Tramitador

            // Si se desmarca un checkbox, limpiar los valores
            if (!$state) {
                $set('valor_comparendo', null);
                $set('valor_transito', null);
                $set('valor_cia', null);
                $set('valor_total_descuento', null);
                $set('valor_tabulado', null);
                $set('valor', null);
                $set('total_value_paymet', null);

                // Desmarcar el otro checkbox si está marcado
                if ($descuentoTipo === '50') {
                    $set('descuento_20', false);
                } else {
                    $set('descuento_50', false);
                }
                return;
            }

            // Asegurarse que solo un checkbox esté marcado
            if ($descuentoTipo === '50') {
                $set('descuento_20', false);
            } else {
                $set('descuento_50', false);
            }

            // Obtener los datos del comparendo
            $comparendo = CategoryRevocation::find($comparendoId);

            if (!$comparendo) {
                return;
            }

            // Establecer los valores según el tipo de descuento
            if ($descuentoTipo === '50') {
                $set('valor_comparendo', $comparendo->subpoena_value);
                $set('valor_transito', $comparendo->transit_pay_50);
                $set('valor_cia', $comparendo->cia_value_50);
                $set('valor_total_descuento', $comparendo->total_discount_50);
            } else {
                $set('valor_comparendo', $comparendo->subpoena_value);
                $set('valor_transito', $comparendo->transit_pay_20);
                $set('valor_cia', $comparendo->cia_value_20);
                $set('valor_total_descuento', $comparendo->total_discount_20);
            }

            // Establecer el valor tabulado (es el mismo para ambos descuentos)
            $set('valor_tabulado', $comparendo->standard_value);

            // Calcular el valor total (CIA + Descuento + Tabulado)
            $valorTotal = $get('valor_cia') + $get('valor_total_descuento') + $get('valor_tabulado');
            $set('valor', $valorTotal);

            // Actualizar el valor total a pagar
            $set('total_value_paymet', $valorTotal);
        }

        function actualizarValorControversia(Set $set, Get $get, $state) {
            $controversiaId = $state;
            $gestion = $get('../../gestion'); // Get if it's Cliente or Tramitador

            if (!$controversiaId) {
                $set('valor_comparendo', null);
                return;
            }

            $controversia = ControversyCategory::find($controversiaId);

            if (!$controversia) {
                return;
            }

            // Set value based on gestion type
            if ($gestion === 'Cliente') {
                $valor = $controversia->client_value;
            } else {
                $valor = $controversia->processor_value;
            }

            $set('valor_comparendo', $valor);
            $set('total_value_paymet', $valor);
        }

        return $form
            ->schema([
                Forms\Components\Group::make()->schema([
                    // Gestion de enrolamiento
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

                    // Informacion del cliente
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

                    //Informacion del Tramitador

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

                    //Procesos

                    Forms\Components\Section::make('Procesos')->schema([
                        Forms\Components\Repeater::make('proceso')
                            ->relationship('proceso')
                            ->schema([
                                Forms\Components\Select::make('processcategory_id')
                                    ->label('Proceso')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(12)
                                    ->relationship('processcategory', 'name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $dni = $get('../../dni');
                                        $set('dni', $dni);
                                    }),
                                Forms\Components\TextInput::make('dni')
                                    ->label('Cédula')
                                    ->live()
                                    ->disabled(function (Get $get) {
                                        $gestion = $get('../../gestion');
                                        return $gestion !== 'Tramitador';
                                    })
                                    ->dehydrated(true)
                                    ->maxLength(11)
                                    ->columnSpan(12)
                                    ->required(),
                                Forms\Components\TextInput::make('sa')
                                    ->label('S.A')
                                    ->hidden(function (Get $get) {
                                        $visibleCategories = ['2', '3', '5']; // Adeudo, Sin Resolución, Prescripción
                                        return !in_array($get('processcategory_id'), $visibleCategories);
                                    })
                                    ->columnSpan(12)
                                    ->maxLength(255)
                                    ->required(fn (Get $get): bool => in_array($get('processcategory_id'), ['2', '3', '5'])),
                                Forms\Components\TextInput::make('ap')
                                    ->label('A.P')
                                    ->hidden(function (Get $get) {
                                        return $get('processcategory_id') !== '4'; // Acuerdo de Pago
                                    })
                                    ->columnSpan(12)
                                    ->maxLength(255)
                                    ->required(fn (Get $get): bool => $get('processcategory_id') === '4'),

                                // Prcesos de acuerdo de pago, adeudo, cobro coativo, prescripciones, sin resolucion, controversia
                                Forms\Components\Section::make('Información del proceso')
                                    ->columns(12)
                                    ->schema([

                                        Forms\Components\Select::make('categorycontroversy_id')
                                            ->label('Categoría de Controversia')
                                            ->options(ControversyCategory::where('is_active', true)->pluck('code', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                actualizarValorControversia($set, $get, $state);
                                            })
                                            ->columnSpan(12)
                                            ->hidden(function (Get $get) {
                                                return $get('processcategory_id') !== '7'; // Only for controversies
                                            }),


                                        Forms\Components\TagsInput::make('comparendo')
                                            ->label('Comparendo')
                                            ->columnSpan(12)
                                            ->required()
                                            ->hidden(function (Get $get) {
                                                return $get('processcategory_id') === '7'; // Hide for controversies
                                            }),

                                        Forms\Components\TextInput::make('valor_comparendo')
                                            ->label(fn (Get $get) => $get('processcategory_id') === '7' ? 'Valor Controversia' : 'Valor Comparendo')
                                            ->numeric()
                                            ->live(onBlur: true)
                                            ->required()
                                            ->columnSpan(6)
                                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                calculateCommissionPruea($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('porcentaje_descuento')
                                            ->label('Descuento (%)')
                                            ->suffix('%')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->live(onBlur: true)
                                            ->hidden(function (Get $get) {
                                                $visibleCategories = ['1', '2', '3', '4', '5'];
                                                return !in_array($get('processcategory_id'), $visibleCategories);
                                            })
                                            ->columnSpan(6)
                                            ->afterStateHydrated(function (Set $set, $state) {
                                                // Asegura que el valor se cargue al editar
                                                $set('porcentaje_descuento', $state ?? 0);
                                            })
                                            ->dehydrated()
                                            ->afterStateUpdated(function (Set $set, Get $get) {
                                                $valorComparendo = $get('valor_comparendo');
                                                $porcentaje = $get('porcentaje_descuento');

                                                if ($valorComparendo && $porcentaje) {
                                                    $descuento = $valorComparendo * ($porcentaje / 100);
                                                    $valorFinal = $valorComparendo - $descuento;
                                                    $set('total_value_paymet', $valorFinal);
                                                }
                                            })
                                            ->dehydrated(),
                                        Forms\Components\DatePicker::make('date_resolution')
                                            ->label('Fecha Resolución')
                                            ->hidden(function (Get $get) {
                                                return $get('processcategory_id') !== '3'; // Solo para Sin Resolución
                                            })
                                            ->columnSpan(12),
                                    ])
                                    ->hidden(function (Get $get) {
                                        $visibleCategories = ['1', '2', '3', '4', '5', '7']; // IDs de los procesos problemáticos
                                        return !in_array($get('processcategory_id'), $visibleCategories);
                                    }),

                                // Detalles de controversias

                                Forms\Components\Section::make('Detalles de la Controversia')
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

                                // Proceso de comparendos

                                Forms\Components\Section::make('Información del Comparendos')
                                    ->columns(12)
                                    ->schema([
                                        // Select para el código del comparendo
                                        Forms\Components\Select::make('categoryrevocation_id')
                                            ->label('Código del Comparendo')
                                            ->options(CategoryRevocation::pluck('code', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                // Limpiar campos cuando cambia el comparendo
                                                $set('descuento_50', false);
                                                $set('descuento_20', false);
                                                $set('valor_comparendo', null);
                                                $set('valor_transito', null);
                                                $set('valor_cia', null);
                                                $set('valor_total_descuento', null);
                                                $set('valor_tabulado', null);
                                                $set('valor', null);
                                                $set('total_value_paymet', null);
                                            })
                                            ->columnSpan(12),

                                        // Checkboxes para los descuentos
                                        Forms\Components\Checkbox::make('descuento_50')
                                            ->label('50% Descuento')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                actualizarValoresComparendo($set, $get, $state, '50');
                                            })
                                            ->columnSpan(6)
                                            ->dehydrated(),

                                        Forms\Components\Checkbox::make('descuento_20')
                                            ->label('20% Descuento')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                actualizarValoresComparendo($set, $get, $state, '20');
                                            })
                                            ->columnSpan(6)
                                            ->dehydrated(),

                                        // Campos para mostrar los valores
                                        Forms\Components\TextInput::make('valor_comparendo')
                                            ->label('Valor del Comparendo')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('valor_transito')
                                            ->label('Valor a pagar tránsito')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('valor_cia')
                                            ->label('Valor CIA')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('valor_total_descuento')
                                            ->label('Valor Total Descuento')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(4),

                                        Forms\Components\TextInput::make('valor_tabulado')
                                            ->label('Valor Tabulado')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(4),

                                        // Campo para la suma automática
                                        Forms\Components\TextInput::make('valor')
                                            ->label('Valor Total (CIA + Descuento + Tabulado)')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(4),
                                    ])
                                    ->hidden(function (Get $get) {
                                        $visibleCategories = ['6'];
                                        return !in_array($get('processcategory_id'), $visibleCategories);
                                    }),

                                // Procesos de Licencias, renovaciones
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
                                        ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                            // Asegurar que se ejecute el cálculo al cargar datos existentes
                                            if ($state) {
                                                calculateTotalCategory($set, $get);
                                            }
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
                                            ->hidden(fn (Get $get) => $get('processcategory_id') == '10')
                                            ->afterStateHydrated(function (Set $set, Get $get) {
                                                // Calcular valor al cargar el formulario
                                                $categories = $get('categoria_licencias');
                                                $totalSchoolLetter = 0;

                                                if (is_array($categories)) {
                                                    foreach ($categories as $categoryId) {
                                                        $category = LicensesSetupCategory::where('name', $categoryId)->first();
                                                        $totalSchoolLetter += $category->school_letter ?? 0;
                                                    }
                                                }

                                                $set('valor_carta_escuela', $totalSchoolLetter);
                                            }),

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

                                Forms\Components\Section::make('Detalles de Licencia')
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
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                if ($state === 'No aplica') {
                                                    $set('valor_examen', 0);
                                                } else {
                                                    // Calcular valor cuando no es "No aplica"
                                                    $categories = $get('categoria_licencias');
                                                    $totalExam = 0;

                                                    if (is_array($categories)) {
                                                        foreach ($categories as $categoryId) {
                                                            $category = LicensesSetupCategory::where('name', $categoryId)->first();
                                                            $totalExam += $category->price_exam ?? 0;
                                                        }
                                                    }

                                                    $set('valor_examen', $totalExam);
                                                }
                                                calculateTotalCategory($set, $get);
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
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                if ($state === 'No aplica') {
                                                    $set('valor_impresion', 0);
                                                } else {
                                                    // Calcular valor cuando no es "No aplica"
                                                    $categories = $get('categoria_licencias');
                                                    $totalSlide = 0;

                                                    if (is_array($categories)) {
                                                        foreach ($categories as $categoryId) {
                                                            $category = LicensesSetupCategory::where('name', $categoryId)->first();
                                                            $totalSlide += $category->price_slide ?? 0;
                                                        }
                                                    }

                                                    $set('valor_impresion', $totalSlide);
                                                }
                                                calculateTotalCategory($set, $get);
                                            }),
                                        Forms\Components\TextInput::make('valor_examen')
                                            ->label('Valor exámenes')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->readOnly()
                                            ->disabled(function (Get $get) {
                                                return $get('examen_medico') === 'No aplica';
                                            })
                                            ->live(),
                                        Forms\Components\TextInput::make('valor_impresion')
                                            ->label('Valor impresión')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->readOnly()
                                            ->disabled(function (Get $get) {
                                                return $get('impresion') === 'No aplica';
                                            })
                                            ->live(),
                                        Forms\Components\Select::make('sin_curso')
                                            ->label('Sin Curso')
                                            ->placeholder('Seleccione una opción')
                                            ->options([
                                                'No aplica' => 'No aplica',
                                                'Aplica' => 'Aplica'
                                            ])
                                            ->columnSpan(6)
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                if ($state === 'No aplica') {
                                                    $set('valor_sin_curso', 0);
                                                } else {
                                                    // Calcular valor cuando es "Aplica"
                                                    $categories = $get('categoria_licencias');
                                                    $totalNoCourse = 0;

                                                    if (is_array($categories)) {
                                                        foreach ($categories as $categoryId) {
                                                            $category = LicensesSetupCategory::where('name', $categoryId)->first();
                                                            $totalNoCourse += $category->price_no_course ?? 0;
                                                        }
                                                    }

                                                    $set('valor_sin_curso', $totalNoCourse);
                                                }
                                                calculateTotalCategory($set, $get);
                                            }),
                                        Forms\Components\TextInput::make('valor_sin_curso')
                                            ->label('Valor Sin Curso')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->readOnly()
                                            ->disabled(function (Get $get) {
                                                return $get('sin_curso') === 'No aplica';
                                            })
                                            ->live(),
                                    ])->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');
                                        return !in_array($processCategoryId, ['9']);
                                    }),

                                // Procesos de Cursos

                                Forms\Components\Section::make('Información del Curso')
                                    ->columns(12)
                                    ->schema([
                                        // Select para el código del curso
                                        Forms\Components\Select::make('coursecategory_id')
                                            ->label('Código del Curso')
                                            ->options(CourseCategory::pluck('code', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                // Limpiar campos cuando cambia el curso
                                                $set('descuento_50', false);
                                                $set('descuento_25', false);
                                                $set('valor_transito', null);
                                                $set('valor', null);
                                                $set('total_value_paymet', null);
                                            })
                                            ->columnSpan(12),

                                        // Checkboxes para los descuentos
                                        Forms\Components\Checkbox::make('descuento_50')
                                            ->label('50% Descuento')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                actualizarValoresCurso($set, $get, $state, '50');
                                            })
                                            ->columnSpan(6)
                                            ->dehydrated(),

                                        Forms\Components\Checkbox::make('descuento_25')
                                            ->label('25% Descuento')
                                            ->live()
                                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                                actualizarValoresCurso($set, $get, $state, '25');
                                            })
                                            ->columnSpan(6)
                                            ->dehydrated(),

                                        // Campos para mostrar los valores
                                        Forms\Components\TextInput::make('valor_transito')
                                            ->label('Valor Tránsito')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(6)
                                            ->dehydrated(),

                                        Forms\Components\TextInput::make('valor')
                                            ->label('Valor')
                                            ->prefix('$')
                                            ->numeric()
                                            ->readOnly()
                                            ->columnSpan(6)
                                            ->afterStateUpdated(function (Set $set, $state) {
                                                $set('total_value_paymet', $state);
                                            })
                                            ->dehydrated(),
                                    ])
                                    ->hidden(fn (Get $get) => $get('processcategory_id') != '8'),

                                // Valor total del proceso

                                Forms\Components\TextInput::make('total_value_paymet')
                                    ->label('Valor')
                                    ->live(onBlur: true)
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(6)
                                    ->afterStateHydrated(function (Set $set, $state, Get $get) {
                                        // Asegurar que el valor se mantenga al editar
                                        if ($state !== null && $state > 0) {
                                            $set('total_value_paymet', $state);
                                        } else {
                                            // Si no hay valor, intentar calcular basado en el tipo de proceso
                                            $processCategoryId = $get('processcategory_id');
                                            if (in_array($processCategoryId, ['9', '10'])) {
                                                calculateTotalCategory($set, $get);
                                            } else if (in_array($processCategoryId, ['1', '2', '4', '5'])) {
                                                calculateCommissionPruea($set, $get);
                                            }
                                        }
                                    })
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
                            ->afterStateHydrated(function (Set $set, Get $get, $state) {
                                // Calcular el total cuando se carga el formulario de edición
                                if ($state) {
                                    $total = 0;
                                    foreach ($state as $item) {
                                        $total += $item['total_value_paymet'] ?? 0;
                                    }
                                    $set('gran_total', $total);
                                }
                            })
                            ->columns(12)
                            ->columnSpan(12)
                            ->addActionLabel('Agregar Proceso'),

                            // Valor total de todos lo procesos registrados

                            Forms\Components\Placeholder::make('grand_value_placeholder')
                                ->label('Valor Total')
                                ->content(function (Get $get, Set $set) {
                                    $total = 0;

                                    // Calcular sumando los valores del repeater
                                    if ($repeaters = $get('proceso')) {
                                        foreach($repeaters as $key => $repeater) {
                                            $value = $get("proceso.{$key}.total_value_paymet") ?? 0;
                                            $total += floatval($value);
                                        }
                                    }

                                    // Actualizar el campo hidden
                                    $set('gran_total', $total);

                                    return Number::currency($total, 'COP');
                                })
                                ->live(),
                            Forms\Components\Hidden::make('gran_total')
                                ->dehydrated()
                                ->live()
                                ->default(0)
                                ->afterStateHydrated(function (Set $set, $state, Get $get) {
                                    // Si no hay valor guardado, recalcular
                                    if ($state === null || $state == 0) {
                                        $total = 0;
                                        if ($repeaters = $get('proceso')) {
                                            foreach($repeaters as $key => $repeater) {
                                                $value = $repeater['total_value_paymet'] ?? 0;
                                                $total += floatval($value);
                                            }
                                        }
                                        $set('gran_total', $total);
                                    }
                                }),
                    ])->columns(2),

                    // Informacion del tramitador para comision
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

                    // Informacion adjuta del estado de cuenta

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

                    // Detalles de estado del los procesos
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
