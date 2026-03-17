<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductTimeslot;
use Carbon\Carbon;

class SkyBrunchSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Crear producto
        $product = Product::create([
            'name' => 'SkyBrunch',
            'default_capacity' => 22,
            'wordpress_product_id' => 1680,
        ]);

        $this->command->info("Producto SkyBrunch creado con ID: {$product->id}");

        // 2️⃣ Crear timeslots por día de la semana (0=domingo, 1=lunes,... 6=sábado)
        // Lunes (1) queda excluido
        for ($weekday = 0; $weekday <= 6; $weekday++) {
            if ($weekday === 1) {
                continue; // lunes cerrado
            }

            ProductTimeslot::create([
                'product_id' => $product->id,
                'weekday' => $weekday,
                'start_time' => '10:00:00',
                'priority' => 10,
                'active' => true,
            ]);
        }

        $this->command->info("Timeslots SkyBrunch creados (10 AM, lunes cerrado)");
    }
}