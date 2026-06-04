<?php

namespace Database\Seeders;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CocinaTestSeeder extends Seeder
{
    /**
     * Genera órdenes de prueba para la Vista de Cocina.
     *
     * La vista (CocinaPage) filtra por:
     *  - booking_start no nulo y con fecha = día seleccionado
     *  - status en processing/completed (excluye pending/draft/failed/cancelled)
     *  - Productos leídos desde data['data']['line_items'][]['meta_data'][] con key '_pao_ids'
     */
    public function run(): void
    {
        // Día de hoy para que aparezca por defecto en la vista.
        $date = Carbon::today();

        // Productos disponibles para el menú (nombre => rango de cantidad).
        $menu = [
            'Menú Degustación Premium',
            'Filet Mignon',
            'Salmón a la Parrilla',
            'Risotto de Hongos',
            'Paella Marinera',
            'Ensalada César',
            'Sopa del Día',
            'Postre Tiramisú',
            'Copa de Champagne',
            'Tabla de Quesos',
        ];

        $nombres = [
            'María González', 'Juan Pérez', 'Ana Rodríguez', 'Carlos Martínez',
            'Lucía Fernández', 'Diego Sánchez', 'Sofía López', 'Miguel Torres',
            'Valentina Ramírez', 'Andrés Gómez', 'Camila Díaz', 'Javier Morales',
            'Isabella Ruiz', 'Sebastián Castro', 'Martina Flores', 'Mateo Vargas',
        ];

        // Horarios del servicio con cantidad de reservas por hora.
        $horarios = [
            '12:00' => 3,
            '13:00' => 2,
            '18:00' => 4,
            '19:00' => 3,
            '20:00' => 4,
            '21:00' => 2,
        ];

        $estados = ['processing', 'processing', 'completed']; // más en preparación

        $nombreIndex = 0;
        $wooId = 900000;

        foreach ($horarios as $hora => $cantidad) {
            [$h, $m] = explode(':', $hora);

            for ($i = 0; $i < $cantidad; $i++) {
                $start = $date->copy()->setTime((int) $h, (int) $m + ($i * 5));
                $end = $start->copy()->addMinutes(90);

                $cliente = $nombres[$nombreIndex % count($nombres)];
                $nombreIndex++;

                // Construir line_items con productos aleatorios.
                $numProductos = rand(2, 4);
                $productosElegidos = collect($menu)->shuffle()->take($numProductos);

                $paoValues = [];
                $totalCantidad = 0;
                foreach ($productosElegidos as $nombreProducto) {
                    $qty = rand(1, 6);
                    $totalCantidad += $qty;
                    $paoValues[] = [
                        'key'   => $nombreProducto,
                        'value' => $qty,
                    ];
                }
                // La vista ignora la entrada "Quantity", la incluimos como en WooCommerce real.
                $paoValues[] = [
                    'key'   => 'Quantity',
                    'value' => $totalCantidad,
                ];

                $data = [
                    'success' => true,
                    'data' => [
                        'id'         => $wooId,
                        'status'     => $estados[array_rand($estados)],
                        'line_items' => [
                            [
                                'id'         => 1,
                                'name'       => 'Reserva Dinner in the Sky',
                                'quantity'   => 1,
                                'meta_data'  => [
                                    [
                                        'key'   => '_pao_ids',
                                        'value' => $paoValues,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'order_notes' => [],
                ];

                $status = $estados[array_rand($estados)];

                Order::create([
                    'woocommerce_order_id' => $wooId,
                    'product_id'           => 101,
                    'customer_name'        => $cliente,
                    'customer_email'       => strtolower(str_replace(' ', '.', $cliente)) . '@example.com',
                    'total'                => rand(150, 800),
                    'status'               => $status,
                    'booking_start'        => $start,
                    'booking_end'          => $end,
                    'data'                 => $data,
                ]);

                $wooId++;
            }
        }

        $this->command->info('Órdenes de prueba para cocina creadas para el ' . $date->format('Y-m-d'));
    }
}
