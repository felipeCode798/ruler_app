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
use App\Models\User;
use App\Models\CategoryRevocation;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\PaymentCourse;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationLabel = 'Cursos';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    public static function form(Form $form): Form
    {

        $roles = Role::whereIn('name', ['cliente'])->get();
        $roleOptions = $roles->pluck('name', 'id')->toArray();

        function updateValue(Set $set, Get $get) {
            $categoryId = $get('categoryrevocation_id');
            $category = CategoryRevocation::find($categoryId);

            if ($category) {
                $set('value_cia', $category->cia_value);
                $set('value_transit', $category->transit_value);
            } else {
                $set('value_cia', 0);
                $set('value_transit', 0);
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
                    Forms\Components\Select::make('categoryrevocation_id')
                        ->label('Categoría')
                        ->placeholder('Seleccione una categoría')
                        ->relationship('categoryrevocation', 'name')
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
                        ->searchable()
                        ->preload(),
                    Forms\Components\TextInput::make('value_commission')
                        ->prefix('$')
                        ->label('Comisión')
                        ->numeric()
                        //->disabled()
                        ->maxLength(11),
                ]),
                Forms\Components\Section::make('Tramite del Curso')
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
                    Forms\Components\Hidden::make('responsible_id')
                        ->default(fn () => Auth::id()),
                ]),
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
                Tables\Columns\TextColumn::make('subpoena')
                    ->label('Comparendo')
                    ->searchable()
                    ->sortable(),
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
                    ->money('USD')
                    ->label('Valor total')
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
                        ExcelExport::make()->fromTable()->only(['client.name','client.dni','subpoena','state','total_value','created_at']),
                    ])
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentCourseRelationManager::class,
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
