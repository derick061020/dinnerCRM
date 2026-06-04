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
                    
                Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-m-device-phone-mobile')
                    ->color('success')
                    ->url(fn (Order $record): string => $this->generateWhatsAppUrl($record))
                    ->openUrlInNewTab(true),
                    
                Action::make('email')
                    ->label('Email')
                    ->icon('heroicon-m-envelope')
                    ->color('info')
                    ->url(fn (Order $record): string => $this->generateEmailUrl($record))
                    ->openUrlInNewTab(true),
                    
                
                    
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
    
    /**
     * Generate WhatsApp URL for customer contact
     */
    protected function generateWhatsAppUrl(Order $record): string
    {
        $phone = $this->extractPhoneNumber($record);
        $message = $this->generateWhatsAppMessage($record);
        
        // Remove any non-digit characters from phone number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Format: https://wa.me/phone?text=message
        return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    }
    
    /**
     * Generate Email URL for customer contact
     */
    protected function generateEmailUrl(Order $record): string
    {
        $email = $record->customer_email;
        $subject = "Información sobre tu orden #{$record->id}";
        $body = $this->generateEmailBody($record);
        
        return "mailto:{$email}?subject=" . urlencode($subject) . "&body=" . urlencode($body);
    }
    
    /**
     * Extract phone number from order data
     */
    protected function extractPhoneNumber(Order $record): string
    {
        // Try to get phone from customer data or order notes
        $phone = '';
        
        // Check if there's phone in customer data (you might need to adjust this based on your data structure)
        if (!empty($record->customer_phone)) {
            $phone = $record->customer_phone;
        }
        
        // If no phone found, return a default or empty string
        return $phone ?: '';
    }
    
    /**
     * Generate WhatsApp message
     */
    protected function generateWhatsAppMessage(Order $record): string
    {
        $message = "Hola {$record->customer_name},\n\n";
        $message .= "Te contacto desde el sistema sobre tu orden #{$record->id}.\n\n";
        $message .= "📋 Detalles de la orden:\n";
        $message .= "• Producto: " . ($record->product?->name ?? 'N/A') . "\n";
        $message .= '• Total: $'.$record->total."\n";
        
        if ($record->booking_start) {
            $message .= "• Fecha de reserva: " . Carbon::parse($record->booking_start)->format('d/m/Y H:i') . "\n";
        }
        
        $message .= "\nPor favor, contáctanos si tienes alguna pregunta.\n";
        $message .= "¡Gracias!";
        
        return $message;
    }
    
    /**
     * Generate email body
     */
    protected function generateEmailBody(Order $record): string
    {
        $body = "Estimado/a {$record->customer_name},\n\n";
        $body .= "Te escribimos desde nuestro sistema sobre tu orden #{$record->id}.\n\n";
        $body .= "Detalles de la orden:\n";
        $body .= "------------------\n";
        $body .= "ID de Orden: {$record->id}\n";
        $body .= "Producto: " . ($record->product?->name ?? 'N/A') . "\n";
        $body .= '• Total: $'.$record->total."\n";
        
        if ($record->booking_start) {
            $body .= "Fecha de reserva: " . Carbon::parse($record->booking_start)->format('d/m/Y H:i') . "\n";
        }
        
        $body .= "Fecha de creación: " . $record->created_at->format('d/m/Y H:i') . "\n";
        $body .= "------------------\n\n";
        $body .= "Si tienes alguna pregunta sobre tu orden, no dudes en contactarnos.\n\n";
        $body .= "Saludos cordiales,\n";
        $body .= "El equipo de soporte";
        
        return $body;
    }
}
