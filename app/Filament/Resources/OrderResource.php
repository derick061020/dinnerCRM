<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    
    protected static ?string $navigationLabel = 'Órdenes';
    
    protected static ?string $modelLabel = 'Orden';
    
    protected static ?string $pluralModelLabel = 'Órdenes';
    
    protected static ?string $navigationGroup = 'Gestión';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Basic Information Section
                Forms\Components\Section::make('Información Básica')
                    ->description('Datos principales de la orden')
                    ->schema([
                        Forms\Components\TextInput::make('customer_name')
                            ->label('Nombre del Cliente')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('customer_email')
                            ->label('Email del Cliente')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->required()
                            ->options([
                                'pending' => 'Pendiente',
                                'processing' => 'Procesando',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                                'refunded' => 'Reembolsado',
                            ])
                            ->default('pending'),
                    ])
                    ->columns(3),
                
                // Product Selection Section
                Forms\Components\Section::make('Selección de Producto')
                    ->description('Elige el producto principal y agrega platos adicionales')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship(name: 'product', titleAttribute: 'name', modifyQueryUsing: fn ($query) => $query->select('id', 'name', 'wordpress_product_id'))
                            ->label('Producto Principal')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('product_name', Product::find($state)?->name ?? null))
                            ->helperText('Selecciona el producto principal de la orden'),
                        
                        // Repeater for additional dishes
                        Forms\Components\Repeater::make('additional_dishes')
                            ->label('Platos Adicionales')
                            ->schema([
                                Forms\Components\TextInput::make('dish_name')
                                    ->label('Nombre del Plato')
                                    ->required()
                                    ->placeholder('Ej: Arroz con Pollo, Ensalada César, etc.')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->placeholder('1')
                                    ->rule('min:1')
                                    ->columnSpan(1),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Notas Especiales')
                                    ->placeholder('Notas sobre este plato (ej: sin cebolla, bien cocido, etc.)')
                                    ->rows(2)
                                    ->columnSpan(3),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['dish_name'] ?? 'Nuevo Plato')
                            ->addActionLabel('Agregar Plato')
                            ->reorderableWithButtons()
                            ->defaultItems(0)
                            ->helperText('Agrega platos adicionales que se incluirán en el producto seleccionado'),
                    ])
                    ->columns(1),
                
                // Booking Information Section
                Forms\Components\Section::make('Información de Reserva')
                    ->description('Fechas y horas de la reserva')
                    ->schema([
                        Forms\Components\DateTimePicker::make('booking_start')
                            ->label('Fecha y Hora de Inicio')
                            ->required()
                            ->displayFormat('d/m/Y H:i')
                            ->withoutSeconds(),
                        Forms\Components\DateTimePicker::make('booking_end')
                            ->label('Fecha y Hora de Fin')
                            ->required()
                            ->displayFormat('d/m/Y H:i')
                            ->withoutSeconds()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $start = $get('booking_start');
                                $total = $get('total');
                                if ($start && $state && $total) {
                                    // Auto-calculate duration if needed
                                    $duration = \Carbon\Carbon::parse($start)->diffInMinutes(\Carbon\Carbon::parse($state));
                                    $set('duration_minutes', $duration);
                                }
                            }),
                    ])
                    ->columns(2),
                
                // Pricing Section
                Forms\Components\Section::make('Información de Precio')
                    ->description('Configura los precios y totales')
                    ->schema([
                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->dehydrateStateUsing(fn ($state) => (float) $state)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $quantity = $get('quantity') ?? 1;
                                $unitPrice = $state / $quantity;
                                $set('unit_price', $unitPrice);
                            }),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->default(1)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $unitPrice = $get('unit_price') ?? 0;
                                $total = $unitPrice * $state;
                                $set('total', $total);
                            })
                            ->rule('min:1'),
                        Forms\Components\TextInput::make('unit_price')
                            ->label('Precio Unitario')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->disabled()
                            ->dehydrateStateUsing(fn ($state) => (float) $state),
                    ])
                    ->columns(3),
                
                // Customer Details Section
                Forms\Components\Section::make('Detalles del Cliente')
                    ->description('Información completa del cliente')
                    ->schema([
                        Forms\Components\TextInput::make('billing_first_name')
                            ->label('Nombre')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('billing_last_name')
                            ->label('Apellido')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('billing_phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('billing_email')
                            ->label('Email de Facturación')
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                
                // Address Section
                Forms\Components\Section::make('Dirección de Facturación')
                    ->description('Dirección completa del cliente')
                    ->schema([
                        Forms\Components\TextInput::make('billing_address_1')
                            ->label('Dirección')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('billing_city')
                            ->label('Ciudad')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('billing_state')
                            ->label('Estado/Provincia')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('billing_postcode')
                            ->label('Código Postal')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('billing_country')
                            ->label('País')
                            ->maxLength(100),
                    ])
                    ->columns(2),
                
                // Payment Information Section
                Forms\Components\Section::make('Información de Pago')
                    ->description('Detalles del método de pago')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Método de Pago')
                            ->options([
                                'cod' => 'Contra entrega (COD)',
                                'bacs' => 'Transferencia Bancaria',
                                'stripe' => 'Tarjeta de Crédito',
                                'paypal' => 'PayPal',
                                'woo_payment' => 'WooCommerce Payment',
                            ])
                            ->default('cod'),
                        Forms\Components\TextInput::make('transaction_id')
                            ->label('ID de Transacción')
                            ->maxLength(100),
                        Forms\Components\Textarea::make('customer_note')
                            ->label('Nota del Cliente')
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->columns(2),
                
                // Additional Notes Section
                Forms\Components\Section::make('Notas Adicionales')
                    ->description('Notas internas y observaciones')
                    ->schema([
                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Notas Internas')
                            ->maxLength(1000)
                            ->rows(4)
                            ->helperText('Notas visibles solo para el staff'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', 'cancelled'))
            ->columns([
                Tables\Columns\TextColumn::make('woocommerce_order_id')
                    ->label('ID')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->size('sm')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->size('sm')
                    ->limit(20)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 20 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('Origen')
                    ->label('Origen')
                    ->getStateUsing(function (Order $record): string {
                        $data = $record->data;
                        if ($data && method_exists($data, 'get')) {
                            foreach ($data->get('data')['meta_data'] as $meta) {
                                if ($meta['key'] === 'pys_enrich_data') {
                                    $pysData = is_string($meta['value']) ? json_decode($meta['value'], true) : $meta['value'];
                                    return $pysData['pys_source'] ?? 'N/A';
                                }
                            }
                        }
                        return 'N/A';
                    })
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('Dirección')
                    ->label('Dirección')
                    ->getStateUsing(function (Order $record): string {
                        $data = $record->data;
                        if ($data && method_exists($data, 'get')) {
                            $billing = $data->get('data')['billing'] ?? [];
                            $address1 = $billing['address_1'] ?? '';
                            $city = $billing['city'] ?? '';
                            
                            $address = trim($address1);
                            if ($city) $address .= ', ' . $city;
                            
                            return $address ?: 'N/A';
                        }
                        return 'N/A';
                    })
                    ->toggleable()
                    ->size('sm')
                    ->limit(25)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 25 ? $state : null;
                    }),
                Tables\Columns\TextColumn::make('Teléfono')
                    ->label('Teléfono')
                    ->getStateUsing(function (Order $record): string {
                        $data = $record->data;
                        if ($data && method_exists($data, 'get')) {
                            return $data->get('data')['billing']['phone'] ?? 'N/A';
                        }
                        return 'N/A';
                    })
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('booking_start')
                    ->label('Reserva')
                    ->dateTime('d/m H:i')
                    ->sortable()
                    ->description(fn ($record): string => 'Inicio')
                    ->toggleable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->size('sm'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'secondary' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'info' => 'refunded',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pend',
                        'processing' => 'Proc',
                        'completed' => 'Comp',
                        'cancelled' => 'Cancel',
                        'refunded' => 'Reemb',
                        default => $state,
                    })
                    ->size('sm'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'processing' => 'Procesando',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                        'refunded' => 'Reembolsado',
                    ]),
                Tables\Filters\SelectFilter::make('product')
                    ->label('Producto')
                    ->relationship(name: 'product', titleAttribute: 'name', modifyQueryUsing: fn ($query) => $query->select('id', 'name', 'wordpress_product_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrderPage::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
