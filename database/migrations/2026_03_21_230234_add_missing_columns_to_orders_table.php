<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Basic order information
            $table->string('currency', 3)->default('USD')->after('status');
            $table->string('version')->nullable()->after('currency');
            $table->boolean('prices_include_tax')->default(false)->after('version');
            $table->decimal('discount_total', 10, 2)->default(0)->after('prices_include_tax');
            $table->decimal('discount_tax', 10, 2)->default(0)->after('discount_total');
            $table->decimal('shipping_total', 10, 2)->default(0)->after('discount_tax');
            $table->decimal('shipping_tax', 10, 2)->default(0)->after('shipping_total');
            $table->decimal('cart_tax', 10, 2)->default(0)->after('shipping_tax');
            $table->decimal('total_tax', 10, 2)->default(0)->after('cart_tax');
            $table->integer('customer_id')->nullable()->after('total_tax');
            $table->string('order_key')->nullable()->after('customer_id');
            
            // Billing information
            $table->string('billing_company')->nullable()->after('billing_country');
            $table->string('billing_address_2')->nullable()->after('billing_address_1');
            
            // Shipping information
            $table->string('shipping_first_name')->nullable()->after('billing_address_2');
            $table->string('shipping_last_name')->nullable()->after('shipping_first_name');
            $table->string('shipping_company')->nullable()->after('shipping_last_name');
            $table->string('shipping_address_1')->nullable()->after('shipping_company');
            $table->string('shipping_address_2')->nullable()->after('shipping_address_1');
            $table->string('shipping_city')->nullable()->after('shipping_address_2');
            $table->string('shipping_state')->nullable()->after('shipping_city');
            $table->string('shipping_postcode')->nullable()->after('shipping_state');
            $table->string('shipping_country')->nullable()->after('shipping_postcode');
            $table->string('shipping_phone')->nullable()->after('shipping_country');
            
            // Payment information
            $table->string('payment_method_title')->nullable()->after('payment_method');
            $table->string('customer_ip_address')->nullable()->after('payment_method_title');
            $table->text('customer_user_agent')->nullable()->after('customer_ip_address');
            $table->string('created_via')->default('checkout')->after('customer_user_agent');
            
            // Order metadata
            $table->string('cart_hash')->nullable()->after('created_via');
            $table->string('order_number')->nullable()->after('cart_hash');
            
            // Dates
            $table->timestamp('date_created')->nullable()->after('order_number');
            $table->timestamp('date_modified')->nullable()->after('date_created');
            $table->timestamp('date_completed')->nullable()->after('date_modified');
            $table->timestamp('date_paid')->nullable()->after('date_completed');
            
            // Additional fields
            $table->decimal('subtotal', 10, 2)->default(0)->after('total');
            $table->decimal('subtotal_tax', 10, 2)->default(0)->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'version',
                'prices_include_tax',
                'discount_total',
                'discount_tax',
                'shipping_total',
                'shipping_tax',
                'cart_tax',
                'total_tax',
                'customer_id',
                'order_key',
                'billing_company',
                'billing_address_2',
                'shipping_first_name',
                'shipping_last_name',
                'shipping_company',
                'shipping_address_1',
                'shipping_address_2',
                'shipping_city',
                'shipping_state',
                'shipping_postcode',
                'shipping_country',
                'shipping_phone',
                'payment_method_title',
                'customer_ip_address',
                'customer_user_agent',
                'created_via',
                'cart_hash',
                'order_number',
                'date_created',
                'date_modified',
                'date_completed',
                'date_paid',
                'subtotal',
                'subtotal_tax'
            ]);
        });
    }
};
