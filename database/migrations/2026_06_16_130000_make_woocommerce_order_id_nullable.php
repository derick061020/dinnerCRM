<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Las ventas manuales no provienen de WooCommerce, por lo que
     * `woocommerce_order_id` debe poder quedar en NULL (limpio).
     * El índice UNIQUE permite múltiples NULL en MySQL, así que se conserva.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `orders` MODIFY `woocommerce_order_id` BIGINT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `orders` MODIFY `woocommerce_order_id` BIGINT NOT NULL');
    }
};
