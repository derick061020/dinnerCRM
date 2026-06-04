<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate WooCommerce order ID if not provided
        if (!isset($data['woocommerce_order_id'])) {
            $data['woocommerce_order_id'] = $this->generateWooCommerceOrderId();
        }

        // Get product information
        $product = Product::find($data['product_id'] ?? null);
        
        // Build the complete data structure for JSON storage
        $orderData = $this->buildOrderDataStructure($data, $product);
        
        // Add the data to be stored in the JSON column
        $data['data'] = $orderData;
        
        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->success()
            ->title('Orden Creada')
            ->body('La orden se ha creado exitosamente con toda la información.')
            ->send();
    }

    /**
     * Generate a unique WooCommerce order ID
     */
    protected function generateWooCommerceOrderId(): int
    {
        $lastOrder = Order::max('woocommerce_order_id') ?? 0;
        return $lastOrder + 1;
    }

    /**
     * Build the complete order data structure for JSON storage
     */
    protected function buildOrderDataStructure(array $formData, ?Product $product): Collection
    {
        $now = now();
        
        // Build line items - start with main product
        $lineItems = [
            [
                'id' => $formData['product_id'] ?? null,
                'name' => $product?->name ?? 'Producto principal',
                'sku' => $product?->sku ?? null,
                'quantity' => $formData['quantity'] ?? 1,
                'price' => $formData['unit_price'] ?? 0,
                'total' => $formData['total'] ?? 0,
                'image' => $product?->image_url ?? null,
                'status' => 'active',
                'meta_data' => $this->buildProductMetaData($product),
            ]
        ];

        // Add additional dishes from repeater
        if (!empty($formData['additional_dishes'])) {
            foreach ($formData['additional_dishes'] as $index => $dish) {
                if (!empty($dish['dish_name'])) {
                    $lineItems[] = [
                        'id' => null, // Additional dishes don't have product IDs
                        'name' => $dish['dish_name'],
                        'sku' => null,
                        'quantity' => $dish['quantity'] ?? 1,
                        'price' => 0, // No price - included in main product
                        'total' => 0, // No total - included in main product
                        'image' => null,
                        'status' => 'active',
                        'meta_data' => [
                            (object) [
                                'key' => 'dish_type',
                                'value' => 'additional_dish',
                            ],
                            (object) [
                                'key' => 'dish_index',
                                'value' => $index + 1,
                            ],
                            (object) [
                                'key' => 'notes',
                                'value' => $dish['notes'] ?? '',
                            ],
                            (object) [
                                'key' => 'included_in_main_product',
                                'value' => 'true',
                            ],
                        ],
                    ];
                }
            }
        }

        // Use only main product total (additional dishes are included)
        $grandTotal = $formData['total'] ?? 0;

        // Build billing data
        $billing = [
            'first_name' => $formData['billing_first_name'] ?? $formData['customer_name'] ?? '',
            'last_name' => $formData['billing_last_name'] ?? '',
            'email' => $formData['billing_email'] ?? $formData['customer_email'] ?? '',
            'phone' => $formData['billing_phone'] ?? '',
            'address_1' => $formData['billing_address_1'] ?? '',
            'city' => $formData['billing_city'] ?? '',
            'state' => $formData['billing_state'] ?? '',
            'postcode' => $formData['billing_postcode'] ?? '',
            'country' => $formData['billing_country'] ?? '',
        ];

        // Build shipping data (same as billing for now)
        $shipping = $billing;

        // Build payment data
        $paymentData = [
            'payment_method' => $formData['payment_method'] ?? 'cod',
            'payment_method_title' => $this->getPaymentMethodTitle($formData['payment_method'] ?? 'cod'),
            'transaction_id' => $formData['transaction_id'] ?? null,
            'date_paid' => $formData['status'] === 'completed' ? $now->format('Y-m-d H:i:s') : null,
        ];

        // Build complete order structure
        $orderStructure = [
            'id' => $formData['woocommerce_order_id'],
            'status' => $formData['status'] ?? 'pending',
            'currency' => 'USD',
            'date_created' => $now->format('Y-m-d H:i:s'),
            'date_modified' => $now->format('Y-m-d H:i:s'),
            'customer_id' => 0, // Guest customer
            'customer_note' => $formData['customer_note'] ?? '',
            'billing' => $billing,
            'shipping' => $shipping,
            'payment_method' => $formData['payment_method'] ?? 'cod',
            'payment_method_title' => $paymentData['payment_method_title'],
            'transaction_id' => $formData['transaction_id'] ?? null,
            'date_paid' => $paymentData['date_paid'],
            'date_completed' => $formData['status'] === 'completed' ? $now->format('Y-m-d H:i:s') : null,
            'cart_hash' => Str::random(32),
            'total' => $grandTotal,
            'total_tax' => 0, // No tax for now
            'subtotal' => $grandTotal,
            'discount_total' => 0,
            'shipping_total' => 0,
            'line_items' => $lineItems,
            'tax_lines' => [],
            'shipping_lines' => [],
            'fee_lines' => [],
            'coupon_lines' => [],
            'refunds' => [],
            'order_notes' => $this->buildInitialOrderNotes($formData),
            'booking_start' => $formData['booking_start'] ?? null,
            'booking_end' => $formData['booking_end'] ?? null,
            'internal_notes' => $formData['internal_notes'] ?? '',
            'additional_dishes_count' => count($formData['additional_dishes'] ?? []),
            'additional_dishes_total' => 0, // Included in main product price
        ];

        return collect($orderStructure);
    }

    /**
     * Build product meta data
     */
    protected function buildProductMetaData(?Product $product): array
    {
        if (!$product) {
            return [];
        }

        return [
            (object) [
                'key' => 'wordpress_product_id',
                'value' => $product->wordpress_product_id,
            ],
            (object) [
                'key' => 'product_type',
                'value' => 'simple',
            ],
            (object) [
                'key' => 'created_at',
                'value' => $product->created_at?->format('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * Build initial order notes
     */
    protected function buildInitialOrderNotes(array $formData): array
    {
        $notes = [];
        
        // Add creation note
        $notes[] = [
            'note' => 'Orden creada manualmente desde el sistema',
            'date_created' => now()->format('Y-m-d H:i:s'),
            'customer_note' => false,
            'added_by' => auth()->user()->name ?? 'System',
        ];

        // Add internal notes if provided
        if (!empty($formData['internal_notes'])) {
            $notes[] = [
                'note' => $formData['internal_notes'],
                'date_created' => now()->format('Y-m-d H:i:s'),
                'customer_note' => false,
                'added_by' => auth()->user()->name ?? 'System',
            ];
        }

        // Add customer note if provided
        if (!empty($formData['customer_note'])) {
            $notes[] = [
                'note' => $formData['customer_note'],
                'date_created' => now()->format('Y-m-d H:i:s'),
                'customer_note' => true,
                'added_by' => $formData['customer_name'] ?? 'Customer',
            ];
        }

        return $notes;
    }

    /**
     * Get payment method title
     */
    protected function getPaymentMethodTitle(string $method): string
    {
        $titles = [
            'cod' => 'Contra entrega (COD)',
            'bacs' => 'Transferencia Bancaria',
            'stripe' => 'Tarjeta de Crédito',
            'paypal' => 'PayPal',
            'woo_payment' => 'WooCommerce Payment',
        ];

        return $titles[$method] ?? $method;
    }
}
