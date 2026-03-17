<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\ProductTimeslot;
use App\Models\Product;
use App\Models\Inventory;
use Carbon\Carbon;
use WpOrg\Requests\Requests;
use Illuminate\Support\Facades\Log;

class WooOrderController extends Controller
{
    public function syncWebhook(Request $request)
    {
        // Registrar siempre la request completa
        Log::info('WooCommerce webhook recibido', ['payload' => $request->all()]);

        // Manejar challenge o payload mínimo
        if ($request->has('webhook_id') && count($request->all()) === 1) {
            return response()->json(['success' => true]);
        }

        try {
            $payload = $request->all();

            // Validar que sea un pedido
            if (!isset($payload['id'], $payload['status'], $payload['line_items'])) {
                throw new \Exception('Payload WooCommerce inválido');
            }

            // Obtener detalles completos usando getOrderDetails para appointment
            $orderDetails = $this->getOrderDetails($payload['id']);
            
            // Logear TODO el response para debugging completo
            Log::info('Response completo de getOrderDetails', [
                'order_id' => $payload['id'],
                'response_type' => gettype($orderDetails),
                'response_success' => $orderDetails->getData()->success ?? false,
                'response_message' => $orderDetails->getData()->message ?? 'No message',
                'response_data_type' => gettype($orderDetails->getData()->data),
                'response_data' => $orderDetails->getData()->data,
                'response_order_notes_type' => gettype($orderDetails->getData()->order_notes),
                'response_order_notes' => $orderDetails->getData()->order_notes,
                'response_original' => $orderDetails->original ?? 'No original'
            ]);
            
            if (!$orderDetails->getData()->success) {
                Log::error('No se pudieron obtener detalles de la orden', ['order_id' => $payload['id']]);
                return response()->json(['success' => false, 'message' => 'No se obtuvieron detalles de la orden' ]);
            }

            $detailsData = $orderDetails->getData()->data;
            
            // Logear los datos obtenidos para debugging
            Log::info('Datos de orden obtenidos', [
                'order_id' => $payload['id'],
                'details_data_type' => gettype($detailsData),
                'details_data' => $detailsData,
                'line_items_count' => isset($detailsData->line_items) ? count($detailsData->line_items) : 0
            ]);
            
            // Extraer appointment_id de los meta_data
            $appointmentId = null;
            $bookingStart = null;
            $bookingEnd = null;
            
            foreach ($detailsData->line_items as $item) {
                if (isset($item->meta_data)) {
                    foreach ($item->meta_data as $meta) {
                        if ($meta->key === '_appointment_id' && !empty($meta->value)) {
                            $appointmentId = is_array($meta->value) ? $meta->value[0] : $meta->value;
                            
                            Log::info('Appointment ID encontrado', [
                                'appointment_id' => $appointmentId,
                                'meta_type' => gettype($meta->value),
                                'meta_value' => $meta->value
                            ]);
                            
                            break 2; // Salir de ambos loops
                        }
                    }
                }
            }

            
            // Si tenemos appointment_id, obtener detalles del appointment
            if ($appointmentId) {
                try {
                    // Usar el endpoint correcto que funciona
                    $appointmentUrl = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc-appointments/v1/appointments/{$appointmentId}";
                    $auth = 'Basic ' . base64_encode('ck_86c1fcde56db9be52be54ef80cb5fdcd73655934:cs_6d8fafab71cd34b6253257457cbcca4415955658');
                    
                    Log::info('Obteniendo appointment con endpoint correcto', [
                        'appointment_id' => $appointmentId,
                        'appointment_url' => $appointmentUrl
                    ]);
                    
                    $appointmentResponse = Requests::get($appointmentUrl, [
                        'Authorization' => $auth,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]);

                    // Logear response completo del appointment
                    Log::info('Response de appointment endpoint correcto', [
                        'appointment_id' => $appointmentId,
                        'response_success' => $appointmentResponse->success,
                        'response_status_code' => $appointmentResponse->status_code ?? 'no status',
                        'response_body_type' => gettype($appointmentResponse->body),
                        'response_body' => $appointmentResponse->body
                    ]);

                    if ($appointmentResponse->success) {
                        $appointmentData = json_decode($appointmentResponse->body, true);
                        $bookingStart = $appointmentData['start'] ?? $appointmentData['date_start'] ?? null;
                        $bookingEnd = $appointmentData['end'] ?? $appointmentData['date_end'] ?? null;
                        
                        Log::info('Appointment obtenido exitosamente', [
                            'appointment_id' => $appointmentId,
                            'appointment_data' => $appointmentData,
                            'start' => $bookingStart,
                            'end' => $bookingEnd
                        ]);
                    } else {
                        Log::error('Error obteniendo appointment', [
                            'appointment_id' => $appointmentId,
                            'status_code' => $appointmentResponse->status_code ?? 'unknown',
                            'body' => $appointmentResponse->body ?? 'no body'
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error obteniendo appointment', [
                        'appointment_id' => $appointmentId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::warning('No se encontró appointment_id', [
                    'order_id' => $payload['id'],
                    'line_items_count' => isset($detailsData->line_items) ? count($detailsData->line_items) : 0
                ]);
            }

            // Extraer el product_id del primer line_item
            $product_id = null;
            if (!empty($payload['line_items'])) {
                $first_item = $payload['line_items'][0];
                $product_id = $first_item['product_id'] ?? null;
            }

            // Guardar pedido en nuestra BD con los datos de booking
            $order = Order::updateOrCreate(
                ['woocommerce_order_id' => $payload['id']],
                [
                    'product_id' => $product_id,
                    'status' => $payload['status'],
                    'customer_name' => $payload['billing']['first_name'] ?? null,
                    'customer_email' => $payload['billing']['email'] ?? null,
                    'total' => $payload['total'] ?? 0,
                    'booking_start' => $bookingStart ? Carbon::parse($bookingStart) : null,
                    'booking_end' => $bookingEnd ? Carbon::parse($bookingEnd) : null,
                    'created_at' => Carbon::parse($payload['date_created']),
                    'updated_at' => Carbon::parse($payload['date_modified']),
                ]
            );

            Log::info('Orden sincronizada exitosamente', [
                'order_id' => $payload['id'],
                'appointment_id' => $appointmentId,
                'booking_start' => $bookingStart,
                'booking_end' => $bookingEnd
            ]);

            // Guardar los productos y actualizar disponibilidad
            foreach ($payload['line_items'] as $item) {
                $product = Product::where('wordpress_product_id', $item['product_id'])->first();
                if ($product) {
                    // Aquí podrías actualizar inventario o slots
                    $product->decrement('default_capacity', $item['quantity']);
                }
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            // Guardar el error y la request completa
            Log::error('Error al procesar webhook WooCommerce', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Endpoint para obtener detalles completos desde WooCommerce
    public function getOrderDetails($woocommerce_order_id)
    {
        $url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc/v3/orders/{$woocommerce_order_id}";

        $response = Requests::get($url, [
            'Authorization' => 'Basic ' . base64_encode('ck_86c1fcde56db9be52be54ef80cb5fdcd73655934:cs_6d8fafab71cd34b6253257457cbcca4415955658')
        ]);

        if(!$response->success){
            return response()->json(['success'=>false,'message'=>'No se pudo obtener datos de WooCommerce']);
        }

        $body = json_decode($response->body, true);

        // Obtener las order notes del pedido
        $notes_url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc/v3/orders/{$woocommerce_order_id}/notes";
        
        $notes_response = Requests::get($notes_url, [
            'Authorization' => 'Basic ' . base64_encode('ck_86c1fcde56db9be52be54ef80cb5fdcd73655934:cs_6d8fafab71cd34b6253257457cbcca4415955658')
        ]);

        $order_notes = [];
        if($notes_response->success){
            $order_notes = json_decode($notes_response->body, true);
        }

        return response()->json([
            'success'=>true,
            'data'=>$body,
            'order_notes'=>$order_notes
        ]);
    }

    // 3. Actualizar disponibilidad del producto
    protected function updateProductAvailability($wordpress_product_id, $date = null, $quantity = 1)
    {
        if(!$wordpress_product_id) return;

        $product = Product::where('wordpress_product_id', $wordpress_product_id)->first();
        if(!$product) return;

        $date = $date ? Carbon::parse($date) : Carbon::today();
        $weekday = $date->dayOfWeek;

        $slots = ProductTimeslot::where('product_id', $product->id)
            ->where('weekday', $weekday)
            ->where('active', true)
            ->get();

        foreach($slots as $slot){
            $inventory = Inventory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'date' => $date->toDateString(),
                    'start_time' => $slot->start_time
                ],
                [
                    'capacity_total' => $product->default_capacity,
                    'capacity_used' => 0
                ]
            );

            $inventory->capacity_used += $quantity;
            $inventory->save();
        }
    }
}