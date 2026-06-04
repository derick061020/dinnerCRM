<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BokunService;
use App\Models\Booking;

class TestBokunIntegration extends Command
{
    protected $signature = 'bokun:test';
    protected $description = 'Test real con Bokun REST v1';

    public function handle()
    {
        $this->info('🚀 Probando integración con Bokun...');

        $bokun = new BokunService();
        
        var_dump($bokun->testCheckoutProper());
        exit();

        $productId = 1181233;
        $date = now()->addDays(1)->format('Y-m-d');
        $endDate = now()->addDays(3)->format('Y-m-d');
        $pax = 1;

        // 1️⃣ Obtener availability
        $this->info("📅 Consultando disponibilidad...");

        $availability = $bokun->getAvailability($productId, $date, $endDate);
        dump($availability);

        if (empty($availability)) {
            $this->error("❌ No hay disponibilidad o error en respuesta");
            dd($availability);
        }

        // 2️⃣ Buscar slot disponible
        $slotFound = null;

        foreach ($availability as $slot) {
            if (($slot['availabilityCount'] ?? 0) >= $pax) {
                $slotFound = $slot;
                break;
            }
        }

        if (!$slotFound) {
            $this->error("❌ No hay cupos disponibles");
            return 1;
        }

        $this->info("✅ Slot encontrado:");
        $this->line(json_encode($slotFound, JSON_PRETTY_PRINT));

        $timeId = $slotFound['id'];
        $startTime = $slotFound['startTime'];

        // 3️⃣ Crear booking
        $this->info("🧾 Creando booking...");

        $this->info("Disponibilidad encontrada ✅. Creando booking...");

        // 🚀 NUEVO FLUJO GUEST
        $response = $bokun->createBookingCheckout(
            $productId,
            $date,
            $timeId,
            2343163,
            1058524
        );

        // 4️⃣ Validar respuesta
        if (!empty($response['bookingId'])) {

            $this->info("🎉 Booking creado!");
            $this->info("🆔 ID: " . $response['bookingId']);

            // Guardar en DB
            Booking::create([
                'woo_order_id' => 'TEST-' . time(),
                'bokun_booking_id' => $response['bookingId'],
                'tour_id' => 1,
                'date' => $date,
                'start_time' => $startTime,
                'pax' => $pax,
                'status' => 'confirmed'
            ]);

        } else {
            $this->error("❌ Error creando booking:");
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
        }

        return 0;
    }
}