<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use Filament\Tables\Actions\ActionGroup;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('Información Personal')
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('dni')
                    ->label('Cedula')
                    ->unique(User::class, 'dni', ignoreRecord: true)
                    ->required()
                    ->maxLength(255)
                    //->disabledOn('edit')
            ]),
            Forms\Components\Section::make('Información de Usuario')
            ->columns(4)
            ->schema([
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
            ]),
            Forms\Components\Section::make('Información de Usuario')
            ->columns(4)
            ->schema([
                Select::make('roles')
                    ->label('Roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->fromTable()->only(['name','email','phone',]),
                    ]),
                ]),
            ]);
    }

    public static function getRelations(): array
    {

        $userId = Request::route('record');
        $user = User::find($userId);

        if ($user && $user->hasRole('Tramitador')) {
            return [
                RelationManagers\ProcessingCommissionsRelationManager::class,
            ];
        }

        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
