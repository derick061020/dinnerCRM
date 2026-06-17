<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Agrega la columna `date_paid` (fecha de pago de WooCommerce) y la rellena
     * desde el JSON `data.data.date_paid_gmt` de las órdenes ya importadas.
     * Las analíticas de dinero agrupan por COALESCE(date_paid, created_at).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'date_paid')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('date_paid')->nullable()->index();
            });
        }

        // Backfill desde el JSON ya guardado (no llama a WooCommerce).
        DB::table('orders')->whereNotNull('data')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $data = json_decode($row->data, true);
                $gmt = $data['data']['date_paid_gmt'] ?? null;
                $local = $data['data']['date_paid'] ?? null;

                if (empty($gmt) && empty($local)) {
                    continue;
                }

                try {
                    $dp = ! empty($gmt)
                        ? Carbon::parse($gmt, 'UTC')
                        : Carbon::parse($local);

                    DB::table('orders')->where('id', $row->id)->update(['date_paid' => $dp]);
                } catch (\Throwable $e) {
                    // Fecha inválida en el JSON: se deja null y se usará created_at como fallback.
                }
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'date_paid')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('date_paid');
            });
        }
    }
};
