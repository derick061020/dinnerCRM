<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    protected static ?string $navigationGroup = 'Administración';

    public static function canViewAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canCreate(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->isAdmin() && $record->id !== auth()->id();
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->isAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                    ->description('Configura los datos básicos del usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('role')
                            ->label('Rol')
                            ->options([
                                'admin' => 'Administrador',
                                'gestor' => 'Gestor',
                            ])
                            ->required()
                            ->default('gestor')
                            ->helperText('Admin: Acceso completo a todos los recursos. Gestor: Acceso limitado.'),

                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state)),

                        Forms\Components\Placeholder::make('password_info')
                            ->label('Información de contraseña')
                            ->content(fn ($record): string => $record ? 
                                'Deja la contraseña en blanco para mantener la actual' : 
                                'La contraseña es obligatoria para nuevos usuarios')
                            ->visible(fn ($context): bool => $context === 'edit'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('role_label')
                    ->label('Rol')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Administrador' => 'danger',
                        'Gestor' => 'warning',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rol')
                    ->options([
                        'admin' => 'Administrador',
                        'gestor' => 'Gestor',
                    ]),

                Tables\Filters\Filter::make('created_today')
                    ->label('Creados hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', now())),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record): bool => auth()->user()->isAdmin()),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record): bool => auth()->user()->isAdmin() && $record->id !== auth()->id())
                    ->before(function (User $record, Tables\Actions\DeleteAction $action) {
                        if ($record->id === auth()->id()) {
                            $action->cancel();
                            
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('No puedes eliminarte a ti mismo')
                                ->body('No puedes eliminar tu propio usuario.')
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn (): bool => auth()->user()->isAdmin())
                    ->before(function (Tables\Actions\DeleteBulkAction $action) {
                        $currentUserId = auth()->id();
                        $selectedUsers = collect($action->getRecords())->pluck('id');
                        
                        if ($selectedUsers->contains($currentUserId)) {
                            $action->cancel();
                            
                            \Filament\Notifications\Notification::make()
                                ->warning()
                                ->title('No puedes eliminarte a ti mismo')
                                ->body('No puedes incluir tu propio usuario en la eliminación masiva.')
                                ->send();
                        }
                    }),
            ])
            ->emptyStateHeading('No hay usuarios registrados')
            ->emptyStateDescription('Crea tu primer usuario para comenzar.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn (): bool => auth()->user()->isAdmin()),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
