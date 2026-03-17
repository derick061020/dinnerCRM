<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\ProductTimeslot; // tu modelo de timeslots
use App\Models\Inventory; // si usas inventario

class AvailabilityController extends Controller
{
    public function getWordpressAvailability(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'min_date' => 'required|date',
            'max_date' => 'required|date'
        ]);
    
        $wordpress_product_id = $request->input('product_id');
        $min_date = $request->input('min_date');
        $max_date = $request->input('max_date');
    
        $product = \App\Models\Product::where('wordpress_product_id', $wordpress_product_id)->first();
        if (!$product) return response()->json(['error'=>'Producto no encontrado'], 404);
    
        $start = \Carbon\Carbon::parse($min_date);
        $end = \Carbon\Carbon::parse($max_date);
    
        $availability_rules = [];
        $order_counter = 1000;
    
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $weekday = $date->dayOfWeek;
    
            // Opcional: excluir días cerrados
            if ($weekday === 1) continue; // lunes cerrado
    
            $slots = \App\Models\ProductTimeslot::where('product_id', $product->id)
                ->where('weekday', $weekday)
                ->where('active', true)
                ->get();
    
            foreach ($slots as $slot) {
                // Revisar inventory si hay reservas
                $inventory = \App\Models\Inventory::where('product_id', $product->id)
                    ->where('date', $date->toDateString())
                    ->where('start_time', $slot->start_time)
                    ->first();
    
                $available = $inventory ? $inventory->capacity_total - $inventory->capacity_used : $product->default_capacity;
    
                // Convertir a "custom:daterange" tal como WooCommerce espera
                $availability_rules[] = [
                    'type' => 'custom:daterange',
                    'range' => [
                        $date->year => [
                            $date->month => [
                                $date->day => [
                                    'from' => $slot->start_time,
                                    'to' => \Carbon\Carbon::parse($slot->start_time)->addHour()->format('H:i'),
                                    'rule' => $available > 0
                                ]
                            ]
                        ]
                    ],
                    'priority' => $slot->priority,
                    'qty' => $available,
                    'level' => 'product',
                    'order' => $order_counter--,
                    'kind_id' => (string)$product->wordpress_product_id
                ];
            }
        }
    
        // Respuesta final en formato WooCommerce
        return response()->json([
            'availability_rules' => $availability_rules,
            'default_availability' => false,
            'appointment_duration' => 1,
            'has_staff' => false,
            'has_staff_ids' => [],
            'staff_assignment' => 'customer',
            'min_date' => $start->timestamp,
            'max_date' => $end->timestamp,
            'partially_scheduled_days' => [],
            'remaining_scheduled_days' => [],
            'fully_scheduled_days' => [],
            'unavailable_days' => [],
            'restricted_days' => false,
            'padding_days' => []
        ]);
    }
    public function getSingleDayAvailability(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'date' => 'required|date'
        ]);

        $wordpress_product_id = $request->input('product_id');
        $date = \Carbon\Carbon::parse($request->input('date'));

        $product = \App\Models\Product::where('wordpress_product_id', $wordpress_product_id)->first();
        if (!$product) {
            return response()->json(['error' => 'Producto no encontrado'], 404);
        }

        $weekday = $date->dayOfWeek;

        // Opcional: excluir días cerrados
        if ($weekday === 1) {
            return response()->json([
                'availability_rules' => [],
                'default_availability' => false,
                'appointment_duration' => 1,
                'has_staff' => false,
                'has_staff_ids' => [],
                'staff_assignment' => 'customer',
                'min_date' => $date->timestamp,
                'max_date' => $date->timestamp
            ]);
        }

        $slots = \App\Models\ProductTimeslot::where('product_id', $product->id)
            ->where('weekday', $weekday)
            ->where('active', true)
            ->get();

        $availability_rules = [];
        $order_counter = 1000;

        foreach ($slots as $slot) {
            $inventory = \App\Models\Inventory::where('product_id', $product->id)
                ->where('date', $date->toDateString())
                ->where('start_time', $slot->start_time)
                ->first();

            $available = $inventory ? $inventory->capacity_total - $inventory->capacity_used : $product->default_capacity;

            $availability_rules[] = [
                'type' => 'custom:daterange',
                'range' => [
                    $date->year => [
                        $date->month => [
                            $date->day => [
                                'from' => \Carbon\Carbon::parse($slot->start_time)->format('H:i'),
                                'to' => \Carbon\Carbon::parse($slot->start_time)->addHour()->format('H:i'),
                                'rule' => $available > 0
                            ]
                        ]
                    ]
                ],
                'priority' => $slot->priority,
                'qty' => $available,
                'level' => 'product',
                'order' => $order_counter--,
                'kind_id' => (string)$product->wordpress_product_id
            ];
        }

        return response()->json([
            'availability_rules' => $availability_rules,
            'default_availability' => false,
            'appointment_duration' => 1,
            'has_staff' => false,
            'has_staff_ids' => [],
            'staff_assignment' => 'customer',
            'min_date' => $date->timestamp,
            'max_date' => $date->timestamp,
            'partially_scheduled_days' => [],
            'remaining_scheduled_days' => [],
            'fully_scheduled_days' => [],
            'unavailable_days' => [],
            'restricted_days' => false,
            'padding_days' => []
        ]);
    }
}