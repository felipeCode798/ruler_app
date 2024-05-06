<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ProcessReturn;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\ProcessingCommission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\ActionGroup;
use App\Models\Category;
use Filament\Forms\Components\TagsInput;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationLabel = 'Cursos';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {

        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function calculateTotalValues(Set $set, Get $get) {
            $categoryId = $get('category_id');
            $category = Category::find($categoryId);
            $cia_des = $category->value_cia_des ?? 0;


            $sumDebit = $get('value_transit') + $cia_des;
            $gainsTotal = ($get('total_value') - $sumDebit);
            $set('total_debit', $sumDebit);
            $set('total_gains', $gainsTotal);
        }

        function updateValue(Set $set, Get $get) {
            $categoryId = $get('category_id');
            $category = Category::find($categoryId);

            if ($category) {
                $set('value_cia', $category->value_cia);
                $set('value_transit', $category->value_transport);
                $set('total_value', $category->price);
                calculateTotalValues($set, $get);
            } else {
                $set('value_cia', 0);
                $set('value_transit', 0);
                $set('total_value', 0);
            }
        }

        function calculateCommission(Set $set, Get $get) {
            $processor = User::find($get('processor_id'));
            $categoryId = $get('category_id');
            $category = Category::find($categoryId);
            $cia_des = $category->value_cia_des ?? 0;

            if ($processor) {
                $commission = $processor->processingCommissions->first()->commission_course ?? 0;

                $sumDebit = $get('value_transit') + $cia_des;
                $commissionTotal = ($get('total_value') - $sumDebit) * ($commission / 100);
                $commissionTotal = round($commissionTotal);

                $set('value_commission', $commissionTotal);
                $set('total_debit', $sumDebit + $commissionTotal);

                $set('total_gains', $get('total_value') - $sumDebit - $commissionTotal);
            } else {
                $set('value_commission', 0);

                $sumDebit = $get('value_transit') + $cia_des;
                $set('total_debit', $sumDebit);
                $set('total_gains', $get('total_value'));
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
                Forms\Components\Section::make('Información del Curso')
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
                    Forms\Components\TextInput::make('value_cia')
                        ->prefix('$')
                        ->label('Valor CIA')
                        ->required()
                        ->numeric()
                        ->live(),
                    Forms\Components\TextInput::make('value_transit')
                        ->prefix('$')
                        ->label('Valor Tránsito')
                        ->required()
                        ->numeric()
                        ->live(),
                    Forms\Components\TextInput::make('total_value')
                        ->prefix('$')
                        ->label('Valor Comparendo')
                        ->required()
                        ->numeric()
                        ->live(),
                ]),
                Forms\Components\Section::make('Documentacion del Curso')
                ->columns(1)
                ->schema([
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
                        ->relationship('processor', 'name', function ($query) { $query->whereHas('roles', function ($roleQuery) { $roleQuery->where('name', 'tramitador'); }); })
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        })
                        ->afterStateHydrated(function (Set $set, Get $get) {
                            calculateCommission($set, $get);
                        })
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('value_commission')
                        ->prefix('$')
                        ->label('Comisión')
                        ->numeric()
                        ->disabled()
                        ->maxLength(11),
                ]),
                Forms\Components\Section::make('Tramite del Curso')
                ->columns(1)
                ->schema([
                    Forms\Components\Select::make('state')
                        ->label('Estado')
                        ->placeholder('Seleccione un estado')
                        ->options([
                            'pending' => 'Pendiente',
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
                    ->disabled()
                    ->prefix('$')
                    ->label('Valor Debito')
                    ->live(),
                    Forms\Components\TextInput::make('total_gains')
                    ->disabled()
                    ->prefix('$')
                    ->label('Valor Ganancias')
                    ->live(),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Course::query()->where('state', '!=', 'return'))
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.dni')
                    ->label('Cedula')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subpoena')
                    ->label('Comparendo')
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
                    Tables\Actions\Action::make('Devolucion', 'start_process')
                        ->requiresConfirmation()
                        ->action(function (Course $Course) {
                            ProcessReturn::create([
                                'user_id' => $Course->user_id,
                                'type_process' => 'courses',
                                'process_id' => $Course->id,
                            ]);

                            $Course->update(['state' => 'return']);

                            return redirect()->back();
                        })
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['client.name', 'subpoena', 'state', 'total_value']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CoursepaymentsRelationManager::class,
            RelationManagers\SupplierCoursePaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
