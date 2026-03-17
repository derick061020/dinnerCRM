<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class PendingOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getTableHeading(): string
    {
        $pendingCount = Order::where('status', 'pending')->count();
        return "Órdenes Pendientes - Atención Requerida ({$pendingCount})";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::where('status', 'pending')
                    ->with('product')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->placeholder('Sin nombre')
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->placeholder('Sin producto')
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                Tables\Columns\TextColumn::make('booking_start')
                    ->label('Fecha Reserva')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin fecha')
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->tooltip('Fecha de creación de la orden')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('customer_email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('Sin email')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('view')
                    ->label('Ver')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Order $record): string => '/orders/' . $record->id)
                    ->openUrlInNewTab(false),
                    
                Action::make('process')
                    ->label('Procesar')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'processing']);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Orden procesada')
                            ->body("La orden #{$record->id} ahora está en proceso")
                            ->send();
                    }),
                    
                Action::make('complete')
                    ->label('Completar')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'completed']);
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Orden completada')
                            ->body("La orden #{$record->id} ha sido completada")
                            ->send();
                    }),
                    
                Action::make('cancel')
                    ->label('Cancelar')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->update(['status' => 'cancelled']);
                        
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Orden cancelada')
                            ->body("La orden #{$record->id} ha sido cancelada")
                            ->send();
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('created_today')
                    ->label('Creadas hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', Carbon::today()))
                    ->indicateUsing(function (array $data): array {
                        if (!($data['created_today'] ?? false)) {
                            return [];
                        }
                        
                        return ['Creadas hoy' => Carbon::today()->format('d/m/Y')];
                    }),
            ])
            ->emptyStateHeading('No hay órdenes pendientes')
            ->emptyStateDescription('Todas las órdenes están procesadas. ¡Buen trabajo!')
            ->emptyStateActions([
                Action::make('view_all_orders')
                    ->label('Ver todas las órdenes')
                    ->icon('heroicon-m-arrow-right')
                    ->url('/orders'),
            ])
            ->paginated([10, 25, 50])
            ->striped()
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50];
    }
}
