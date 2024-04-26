<?php

namespace App\Filament\Personal\Resources;

use App\Filament\Personal\Resources\ControversyResource\Pages;
use App\Filament\Personal\Resources\ControversyResource\RelationManagers;
use App\Models\Controversy;
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
use Illuminate\Support\Facades\Auth;

class ControversyResource extends Resource
{
    protected static ?string $model = Controversy::class;
    protected static ?string $navigationLabel = 'Controversias';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Form $form): Form
    {
        function calculateCommission(Set $set, Get $get) {
            $processor = User::find($get('processor_id'));

            if ($processor) {
                $commission = $processor->processingCommissions->first()->prescriptions_commission ?? 0;

                $commissionTotal = ($get('total_value')) * ($commission / 100);
                $commissionTotal = round($commissionTotal);

                $set('value_commission', $commissionTotal);
                $set('total_debit',  $commissionTotal);

                $set('total_gains', $get('total_value') - $commissionTotal);
            } else {
                $set('value_commission', 0);

                $set('total_debit', 0);
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
            ->columns(4)
            ->schema([
                Forms\Components\DateTimePicker::make('appointment')
                    ->label('Cita')
                    ->required()
                    ->maxDate(now()),
                Forms\Components\TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('window')
                    ->label('Ventana')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_value')
                    ->label('Valor total')
                    ->required()
                    ->numeric()
                    ->maxLength(11),
            ]),
            Forms\Components\Section::make('Documentacion de la Controversia')
            ->columns(2)
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
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        calculateCommission($set, $get);
                    })
                    ->afterStateHydrated(function (Set $set, Get $get) {
                        calculateCommission($set, $get);
                    }),
                Forms\Components\TextInput::make('value_commission')
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
                    'return' => 'Devuelto',
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
                ->label('Valor Debito')
                ->live(),
                Forms\Components\TextInput::make('total_gains')
                ->label('Valor Ganancias')
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
                    ->label('Valor total')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BooleanColumn::make('paid')
                    ->label('Pagado')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Descargar', 'download')
                    ->action(function (Controversy $controversy) {
                        return redirect()->route('download', [
                            'filename' => $controversy->document_dni,
                            'filename2' => $controversy->document_power,
                        ]);
                    }),
                Tables\Actions\Action::make('Iniciar Proceso', 'start_process')
                    ->action(function (Controversy $controversy) {
                        // Lógica para insertar en processreturn
                        ProcessReturn::create([
                            'user_id' => $controversy->user_id,
                            'type_process' => 'controversies',
                            'process_id' => $controversy->id,
                        ]);

                        $controversy->update(['state' => 'return']);

                        return redirect()->back();
                    }),
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
            //
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
