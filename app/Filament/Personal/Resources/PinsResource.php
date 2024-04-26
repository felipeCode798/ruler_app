<?php

namespace App\Filament\Personal\Resources;

use App\Filament\Personal\Resources\PinsResource\Pages;
use App\Filament\Personal\Resources\PinsResource\RelationManagers;
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
use Illuminate\Support\Facades\Auth;

class PinsResource extends Resource
{
    protected static ?string $model = Pins::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Gestión de Licencias';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

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
                        ->required(),
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
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría'),
                Tables\Columns\TextColumn::make('enlistment')
                    ->label('Enrrolamiento'),
                Tables\Columns\TextColumn::make('certificate')
                    ->label('Certificado'),
                Tables\Columns\TextColumn::make('state')
                    ->label('Estado'),
                Tables\Columns\TextColumn::make('observations')
                    ->label('Observaciones'),
                Tables\Columns\TextColumn::make('processor.name')
                    ->label('Tramitador'),
                Tables\Columns\TextColumn::make('value_commission')
                    ->label('Comisión'),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valor total'),
                Tables\Columns\TextColumn::make('payments_sum')
                    ->label('Pagos')
                    ->searchable()
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPins::route('/'),
            'create' => Pages\CreatePins::route('/create'),
            'edit' => Pages\EditPins::route('/{record}/edit'),
        ];
    }
}
