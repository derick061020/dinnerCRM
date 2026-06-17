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
        // Idempotente: solo agrega las columnas que falten. La corrida original quedó
        // a medias (algunas columnas ya existían), así que cada add se protege con hasColumn.
        Schema::table('orders', function (Blueprint $table) {
            $add = function (string $name, callable $define) use ($table) {
                if (! Schema::hasColumn('orders', $name)) {
                    $define($table);
                }
            };

            // Basic order information
            $add('currency', fn ($t) => $t->string('currency', 3)->default('USD')->after('status'));
            $add('version', fn ($t) => $t->string('version')->nullable()->after('currency'));
            $add('prices_include_tax', fn ($t) => $t->boolean('prices_include_tax')->default(false)->after('version'));
            $add('discount_total', fn ($t) => $t->decimal('discount_total', 10, 2)->default(0)->after('prices_include_tax'));
            $add('discount_tax', fn ($t) => $t->decimal('discount_tax', 10, 2)->default(0)->after('discount_total'));
            $add('shipping_total', fn ($t) => $t->decimal('shipping_total', 10, 2)->default(0)->after('discount_tax'));
            $add('shipping_tax', fn ($t) => $t->decimal('shipping_tax', 10, 2)->default(0)->after('shipping_total'));
            $add('cart_tax', fn ($t) => $t->decimal('cart_tax', 10, 2)->default(0)->after('shipping_tax'));
            $add('total_tax', fn ($t) => $t->decimal('total_tax', 10, 2)->default(0)->after('cart_tax'));
            $add('customer_id', fn ($t) => $t->integer('customer_id')->nullable()->after('total_tax'));
            $add('order_key', fn ($t) => $t->string('order_key')->nullable()->after('customer_id'));

            // Billing information
            $add('billing_company', fn ($t) => $t->string('billing_company')->nullable()->after('billing_country'));
            $add('billing_address_2', fn ($t) => $t->string('billing_address_2')->nullable()->after('billing_address_1'));

            // Shipping information
            $add('shipping_first_name', fn ($t) => $t->string('shipping_first_name')->nullable()->after('billing_address_2'));
            $add('shipping_last_name', fn ($t) => $t->string('shipping_last_name')->nullable()->after('shipping_first_name'));
            $add('shipping_company', fn ($t) => $t->string('shipping_company')->nullable()->after('shipping_last_name'));
            $add('shipping_address_1', fn ($t) => $t->string('shipping_address_1')->nullable()->after('shipping_company'));
            $add('shipping_address_2', fn ($t) => $t->string('shipping_address_2')->nullable()->after('shipping_address_1'));
            $add('shipping_city', fn ($t) => $t->string('shipping_city')->nullable()->after('shipping_address_2'));
            $add('shipping_state', fn ($t) => $t->string('shipping_state')->nullable()->after('shipping_city'));
            $add('shipping_postcode', fn ($t) => $t->string('shipping_postcode')->nullable()->after('shipping_state'));
            $add('shipping_country', fn ($t) => $t->string('shipping_country')->nullable()->after('shipping_postcode'));
            $add('shipping_phone', fn ($t) => $t->string('shipping_phone')->nullable()->after('shipping_country'));

            // Payment information
            $add('payment_method_title', fn ($t) => $t->string('payment_method_title')->nullable()->after('payment_method'));
            $add('customer_ip_address', fn ($t) => $t->string('customer_ip_address')->nullable()->after('payment_method_title'));
            $add('customer_user_agent', fn ($t) => $t->text('customer_user_agent')->nullable()->after('customer_ip_address'));
            $add('created_via', fn ($t) => $t->string('created_via')->default('checkout')->after('customer_user_agent'));

            // Order metadata
            $add('cart_hash', fn ($t) => $t->string('cart_hash')->nullable()->after('created_via'));
            $add('order_number', fn ($t) => $t->string('order_number')->nullable()->after('cart_hash'));

            // Dates
            $add('date_created', fn ($t) => $t->timestamp('date_created')->nullable()->after('order_number'));
            $add('date_modified', fn ($t) => $t->timestamp('date_modified')->nullable()->after('date_created'));
            $add('date_completed', fn ($t) => $t->timestamp('date_completed')->nullable()->after('date_modified'));
            $add('date_paid', fn ($t) => $t->timestamp('date_paid')->nullable()->after('date_completed'));

            // Additional fields
            $add('subtotal', fn ($t) => $t->decimal('subtotal', 10, 2)->default(0)->after('total'));
            $add('subtotal_tax', fn ($t) => $t->decimal('subtotal_tax', 10, 2)->default(0)->after('subtotal'));
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
