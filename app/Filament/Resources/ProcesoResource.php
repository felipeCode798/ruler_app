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
                        Forms\Components\Repeater::make('proceso')
                            ->relationship('proceso')
                            ->schema([
                                Forms\Components\Select::make('processcategory_id')
                                    ->label('Proceseso')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(12)
                                    ->relationship('processcategory', 'name')
                                    ->reactive(),
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
                                            ->columnSpan(4),
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
                                            ->live(),
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
                                            ->columnSpan(6),
                                        Forms\Components\Select::make('impresion')
                                            ->label('Impresión')
                                            ->placeholder('Seleccione un estado')
                                            ->options([
                                                'Pendiente' => 'Pendiente',
                                                'Finalizado' => 'Finalizado',
                                                'Devuelto' => 'Devuelto'
                                            ])
                                            ->columnSpan(6),
                                        Forms\Components\TextInput::make('valor_examen')
                                            ->label('Valor exámenes')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->maxLength(11)
                                            ->live(),
                                        Forms\Components\TextInput::make('valor_impresion')
                                            ->label('Valor impresión')
                                            ->prefix('$')
                                            ->numeric()
                                            ->columnSpan(6)
                                            ->maxLength(11)
                                            ->live(),
                                    ])->hidden(function (Get $get) {
                                        $processCategoryId = $get('processcategory_id');

                                        if($processCategoryId === '9'){
                                            return false;
                                        }else if($processCategoryId === '10'){
                                            return false;
                                        }
                                        return true;
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
                            ])->columns(12)
                            ->columnSpan(12)
                            ->addActionLabel('Agregar Controversia'),

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
                    // Tables\Actions\Action::make('Documentos')
                    //     ->label('Estado de Cuenta')
                    //     ->hidden(function (Proceso $proceso) {
                    //         return !$proceso->estado_cuenta;
                    //     })
                    //     ->action(function (Proceso $proceso) {
                    //         return redirect()->route('estado.de.cuenta.download', [
                    //             'filename' => $proceso->estado_cuenta,
                    //         ]);
                    //     }),
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
