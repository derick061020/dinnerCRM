<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderDataMigrationService
{
    /**
     * Populate order columns from JSON data
     */
    public function populateOrderColumns(Order $order): Order
    {
        if (!$order->data) {
            return $order;
        }

        try {
            $data = is_string($order->data) ? json_decode($order->data, true) : $order->data;
            
            if (!isset($data['data'])) {
                return $order;
            }

            $orderData = $data['data'];

            // Basic order information
            $order->currency = $orderData['currency'] ?? 'USD';
            $order->version = $orderData['version'] ?? null;
            $order->prices_include_tax = $orderData['prices_include_tax'] ?? false;
            $order->discount_total = $orderData['discount_total'] ?? 0;
            $order->discount_tax = $orderData['discount_tax'] ?? 0;
            $order->shipping_total = $orderData['shipping_total'] ?? 0;
            $order->shipping_tax = $orderData['shipping_tax'] ?? 0;
            $order->cart_tax = $orderData['cart_tax'] ?? 0;
            $order->total_tax = $orderData['total_tax'] ?? 0;
            $order->customer_id = $orderData['customer_id'] ?? null;
            $order->order_key = $orderData['order_key'] ?? null;

            // Billing information
            if (isset($orderData['billing'])) {
                $billing = $orderData['billing'];
                $order->billing_first_name = $billing['first_name'] ?? null;
                $order->billing_last_name = $billing['last_name'] ?? null;
                $order->billing_company = $billing['company'] ?? null;
                $order->billing_address_1 = $billing['address_1'] ?? null;
                $order->billing_address_2 = $billing['address_2'] ?? null;
                $order->billing_city = $billing['city'] ?? null;
                $order->billing_state = $billing['state'] ?? null;
                $order->billing_postcode = $billing['postcode'] ?? null;
                $order->billing_country = $billing['country'] ?? null;
                $order->billing_email = $billing['email'] ?? null;
                $order->billing_phone = $billing['phone'] ?? null;
            }

            // Shipping information
            if (isset($orderData['shipping'])) {
                $shipping = $orderData['shipping'];
                $order->shipping_first_name = $shipping['first_name'] ?? null;
                $order->shipping_last_name = $shipping['last_name'] ?? null;
                $order->shipping_company = $shipping['company'] ?? null;
                $order->shipping_address_1 = $shipping['address_1'] ?? null;
                $order->shipping_address_2 = $shipping['address_2'] ?? null;
                $order->shipping_city = $shipping['city'] ?? null;
                $order->shipping_state = $shipping['state'] ?? null;
                $order->shipping_postcode = $shipping['postcode'] ?? null;
                $order->shipping_country = $shipping['country'] ?? null;
                $order->shipping_phone = $shipping['phone'] ?? null;
            }

            // Payment information
            $order->payment_method = $orderData['payment_method'] ?? null;
            $order->payment_method_title = $orderData['payment_method_title'] ?? null;
            $order->transaction_id = $orderData['transaction_id'] ?? null;
            $order->customer_ip_address = $orderData['customer_ip_address'] ?? null;
            $order->customer_user_agent = $orderData['customer_user_agent'] ?? null;
            $order->created_via = $orderData['created_via'] ?? 'checkout';
            $order->customer_note = $orderData['customer_note'] ?? null;

            // Order metadata
            $order->cart_hash = $orderData['cart_hash'] ?? null;
            $order->order_number = $orderData['number'] ?? null;

            // Dates
            $order->date_created = $this->parseDate($orderData['date_created'] ?? null);
            $order->date_modified = $this->parseDate($orderData['date_modified'] ?? null);
            $order->date_completed = $this->parseDate($orderData['date_completed'] ?? null);
            $order->date_paid = $this->parseDate($orderData['date_paid'] ?? null);

            // Calculate subtotal from line items
            if (isset($orderData['line_items']) && is_array($orderData['line_items'])) {
                $subtotal = 0;
                $subtotalTax = 0;
                
                foreach ($orderData['line_items'] as $item) {
                    $subtotal += floatval($item['subtotal'] ?? 0);
                    $subtotalTax += floatval($item['subtotal_tax'] ?? 0);
                }
                
                $order->subtotal = $subtotal;
                $order->subtotal_tax = $subtotalTax;
            }

            return $order;

        } catch (\Exception $e) {
            Log::error('Error populating order columns from JSON', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return $order;
        }
    }

    /**
     * Parse date from WooCommerce format
     */
    private function parseDate($dateString): ?\Carbon\Carbon
    {
        if (!$dateString) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Migrate all orders with JSON data to individual columns
     */
    public function migrateAllOrders(): int
    {
        $orders = Order::whereNotNull('data')->where('data', '!=', '')->get();
        $migrated = 0;

        foreach ($orders as $order) {
            $this->populateOrderColumns($order);
            $order->save();
            $migrated++;
        }

        return $migrated;
    }

    /**
     * Create order from WooCommerce API data
     */
    public function createOrderFromWooData(array $wooData): Order
    {
        if (!isset($wooData['data'])) {
            throw new \InvalidArgumentException('Invalid WooCommerce data format');
        }

        $orderData = $wooData['data'];
        
        $order = new Order([
            'woocommerce_order_id' => $orderData['id'],
            'status' => $orderData['status'],
            'total' => $orderData['total'],
            'currency' => $orderData['currency'] ?? 'USD',
            'version' => $orderData['version'] ?? null,
            'prices_include_tax' => $orderData['prices_include_tax'] ?? false,
            'discount_total' => $orderData['discount_total'] ?? 0,
            'discount_tax' => $orderData['discount_tax'] ?? 0,
            'shipping_total' => $orderData['shipping_total'] ?? 0,
            'shipping_tax' => $orderData['shipping_tax'] ?? 0,
            'cart_tax' => $orderData['cart_tax'] ?? 0,
            'total_tax' => $orderData['total_tax'] ?? 0,
            'customer_id' => $orderData['customer_id'] ?? null,
            'order_key' => $orderData['order_key'] ?? null,
            'payment_method' => $orderData['payment_method'] ?? null,
            'payment_method_title' => $orderData['payment_method_title'] ?? null,
            'transaction_id' => $orderData['transaction_id'] ?? null,
            'customer_ip_address' => $orderData['customer_ip_address'] ?? null,
            'customer_user_agent' => $orderData['customer_user_agent'] ?? null,
            'created_via' => $orderData['created_via'] ?? 'checkout',
            'customer_note' => $orderData['customer_note'] ?? null,
            'cart_hash' => $orderData['cart_hash'] ?? null,
            'order_number' => $orderData['number'] ?? null,
            'date_created' => $this->parseDate($orderData['date_created'] ?? null),
            'date_modified' => $this->parseDate($orderData['date_modified'] ?? null),
            'date_completed' => $this->parseDate($orderData['date_completed'] ?? null),
            'date_paid' => $this->parseDate($orderData['date_paid'] ?? null),
        ]);

        // Set billing information
        if (isset($orderData['billing'])) {
            $billing = $orderData['billing'];
            $order->billing_first_name = $billing['first_name'] ?? null;
            $order->billing_last_name = $billing['last_name'] ?? null;
            $order->billing_company = $billing['company'] ?? null;
            $order->billing_address_1 = $billing['address_1'] ?? null;
            $order->billing_address_2 = $billing['address_2'] ?? null;
            $order->billing_city = $billing['city'] ?? null;
            $order->billing_state = $billing['state'] ?? null;
            $order->billing_postcode = $billing['postcode'] ?? null;
            $order->billing_country = $billing['country'] ?? null;
            $order->billing_email = $billing['email'] ?? null;
            $order->billing_phone = $billing['phone'] ?? null;
            
            // Set legacy fields for compatibility
            $order->customer_name = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
            $order->customer_email = $billing['email'] ?? null;
        }

        // Set shipping information
        if (isset($orderData['shipping'])) {
            $shipping = $orderData['shipping'];
            $order->shipping_first_name = $shipping['first_name'] ?? null;
            $order->shipping_last_name = $shipping['last_name'] ?? null;
            $order->shipping_company = $shipping['company'] ?? null;
            $order->shipping_address_1 = $shipping['address_1'] ?? null;
            $order->shipping_address_2 = $shipping['address_2'] ?? null;
            $order->shipping_city = $shipping['city'] ?? null;
            $order->shipping_state = $shipping['state'] ?? null;
            $order->shipping_postcode = $shipping['postcode'] ?? null;
            $order->shipping_country = $shipping['country'] ?? null;
            $order->shipping_phone = $shipping['phone'] ?? null;
        }

        // Calculate subtotal from line items
        if (isset($orderData['line_items']) && is_array($orderData['line_items'])) {
            $subtotal = 0;
            $subtotalTax = 0;
            
            foreach ($orderData['line_items'] as $item) {
                $subtotal += floatval($item['subtotal'] ?? 0);
                $subtotalTax += floatval($item['subtotal_tax'] ?? 0);
                
                // Set product_id from first item if not set
                if (!$order->product_id && isset($item['product_id'])) {
                    $order->product_id = $item['product_id'];
                    $order->quantity = $item['quantity'] ?? 1;
                    $order->unit_price = $item['price'] ?? 0;
                }
            }
            
            $order->subtotal = $subtotal;
            $order->subtotal_tax = $subtotalTax;
        }

        return $order;
    }
}
