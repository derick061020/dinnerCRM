<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderDataMigrationService;
use Illuminate\Console\Command;
use WpOrg\Requests\Requests;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ImportWooOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:import-woo {--status=any : Order status to import (use "any" for all statuses)} {--limit=100 : Number of orders to import per page} {--all : Import ALL orders without limit} {--retries=3 : Number of retries for failed requests} {--force : Force import without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import WooCommerce orders using the same logic as syncWebhook';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $status = $this->option('status');
        $limit = $this->option('limit');
        $importAll = $this->option('all');
        $maxRetries = $this->option('retries');
        
        if (!$this->option('force')) {
            if ($importAll) {
                if (!$this->confirm("This will import ALL WooCommerce orders with all statuses. This could take a long time. Do you want to continue?")) {
                    $this->info('Operation cancelled.');
                    return 0;
                }
            } else {
                $statusText = $status === 'any' ? 'all statuses' : "status '{$status}'";
                if (!$this->confirm("This will import {$limit} WooCommerce orders with {$statusText}. Do you want to continue?")) {
                    $this->info('Operation cancelled.');
                    return 0;
                }
            }
        }

        $this->info('Starting WooCommerce orders import...');
        $this->info("Max retries per request: {$maxRetries}");

        try {
            if ($importAll) {
                // Importar página por página
                $importedCount = $this->importAllOrdersPageByPage($status, $maxRetries);
                $errorCount = 0; // El método interno maneja los errores
            } else {
                // Importar con límite (comportamiento anterior)
                $orders = $this->getWooOrders($status, $limit, $maxRetries);
                
                if (empty($orders)) {
                    $this->info('No orders found to import.');
                    return 0;
                }

                $this->info("Found " . count($orders) . " orders to import.");
                
                $importedCount = 0;
                $errorCount = 0;
                
                foreach ($orders as $order) {
                    try {
                        // Obtener detalles completos y order notes con reintentos
                        $orderDetails = $this->getOrderDetailsWithRetry($order['id'], $maxRetries);
                        
                        if (!$orderDetails->getData()->success) {
                            $errorCount++;
                            $this->error("✗ Failed to get details for order #{$order['id']} after {$maxRetries} retries");
                            continue;
                        }
                        
                        $detailsData = $orderDetails->getData()->data;
                        $orderNotes = $orderDetails->getData()->order_notes;
                        
                        // Extraer appointment_id de las order notes
                        $appointmentId = null;
                        $bookingStart = null;
                        $bookingEnd = null;
                        
                        foreach ($orderNotes as $note) {
                            if (isset($note->note) && preg_match('/Appointment #(\d+)/', $note->note, $matches)) {
                                $appointmentId = $matches[1];
                                break;
                            }
                        }
                        
                        // Si tenemos appointment_id, obtener detalles del appointment con reintentos
                        if ($appointmentId) {
                            try {
                                $bookingData = $this->getAppointmentDetailsWithRetry($appointmentId, $maxRetries);
                                if ($bookingData) {
                                    $bookingStart = $bookingData['start'] ?? $bookingData['date_start'] ?? null;
                                    $bookingEnd = $bookingData['end'] ?? $bookingData['date_end'] ?? null;
                                }
                            } catch (\Exception $e) {
                                Log::error('Error obteniendo appointment después de reintentos', [
                                    'appointment_id' => $appointmentId,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        // Extraer product_id del primer line_item
                        $product_id = null;
                        if (!empty($detailsData->line_items)) {
                            $first_item = $detailsData->line_items[0];
                            $product_id = $first_item->product_id ?? null;
                        }
                        
                        // Guardar pedido en nuestra BD usando el mismo enfoque que syncWebhook
                        try {
                            // Preparar datos completos como en el controller
                            $completeData = [
                                'success' => true,
                                'data' => $detailsData,
                                'order_notes' => $orderNotes
                            ];
                            
                            // Crear orden directamente como en el controller
                            $order = Order::updateOrCreate(
                                ['woocommerce_order_id' => $order['id']],
                                [
                                    'product_id' => $product_id,
                                    'status' => $detailsData->status,
                                    'customer_name' => $detailsData->billing->first_name ?? null,
                                    'customer_email' => $detailsData->billing->email ?? null,
                                    'total' => $detailsData->total ?? 0,
                                    'booking_start' => $bookingStart ? Carbon::parse($bookingStart) : null,
                                    'booking_end' => $bookingEnd ? Carbon::parse($bookingEnd) : null,
                                    'data' => $completeData,
                                    'created_at' => Carbon::parse($detailsData->date_created),
                                    'updated_at' => Carbon::parse($detailsData->date_modified),
                                ]
                            );
                            
                            $importedCount++;
                            $this->line("✓ Imported order #{$order['id']}");
                            
                        } catch (\Exception $e) {
                            Log::error('Error creando orden', [
                                'error' => $e->getMessage(),
                                'order_id' => $order['id']
                            ]);
                            
                            $errorCount++;
                            $this->error("✗ Error importing order #{$order['id']}: " . $e->getMessage());
                        }
                        
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->error("✗ Error importing order #{$order['id']}: " . $e->getMessage());
                        Log::error('Error importing WooCommerce order in command', [
                            'order_id' => $order['id'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $this->info("\nImport completed:");
            $this->info("✓ Successfully imported: {$importedCount} orders");
            if ($errorCount > 0) {
                $this->error("✗ Failed to import: {$errorCount} orders");
            }
            
            return $errorCount > 0 ? 1 : 0;
            
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            Log::error('WooCommerce import command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Obtener órdenes desde WooCommerce API
     */
    private function getWooOrders($status, $limit, $maxRetries = 3)
    {
        $url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc/v3/orders";
        
        $params = [
            'per_page' => $limit,
            'orderby' => 'date',
            'order' => 'desc'
        ];
        
        // Solo agregar status si no es 'any'
        if ($status !== 'any') {
            $params['status'] = $status;
        }
        
        $queryString = http_build_query($params);
        $fullUrl = $url . '?' . $queryString;
        
        return $this->makeRequestWithRetry($fullUrl, $maxRetries);
    }

    /**
     * Importar todas las órdenes página por página (más eficiente en memoria)
     */
    private function importAllOrdersPageByPage($status, $maxRetries = 3)
    {
        $this->info('Starting page-by-page import of ALL orders...');
        
        $totalImported = 0;
        $totalErrors = 0;
        $page = 1;
        $perPage = 100;
        
        do {
            $this->info("\n=== Processing page {$page} ===");
            
            try {
                // Obtener una página de órdenes
                $orders = $this->getOrdersPage($page, $perPage, $status, $maxRetries);
                
                if (empty($orders)) {
                    $this->info("No more orders found. Ending import.");
                    break;
                }
                
                $this->info("Found " . count($orders) . " orders on page {$page}");
                
                // Procesar esta página
                $pageImported = 0;
                $pageErrors = 0;
                
                foreach ($orders as $order) {
                    try {
                        // Obtener detalles completos y order notes con reintentos
                        $orderDetails = $this->getOrderDetailsWithRetry($order['id'], $maxRetries);
                        
                        if (!$orderDetails->getData()->success) {
                            $pageErrors++;
                            $this->error("✗ Failed to get details for order #{$order['id']} after {$maxRetries} retries");
                            continue;
                        }
                        
                        $detailsData = $orderDetails->getData()->data;
                        $orderNotes = $orderDetails->getData()->order_notes;
                        
                        // Extraer appointment_id de las order notes
                        $appointmentId = null;
                        $bookingStart = null;
                        $bookingEnd = null;
                        
                        foreach ($orderNotes as $note) {
                            if (isset($note->note) && preg_match('/Appointment #(\d+)/', $note->note, $matches)) {
                                $appointmentId = $matches[1];
                                break;
                            }
                        }
                        
                        // Si tenemos appointment_id, obtener detalles del appointment con reintentos
                        if ($appointmentId) {
                            try {
                                $bookingData = $this->getAppointmentDetailsWithRetry($appointmentId, $maxRetries);
                                if ($bookingData) {
                                    $bookingStart = $bookingData['start'] ?? $bookingData['date_start'] ?? null;
                                    $bookingEnd = $bookingData['end'] ?? $bookingData['date_end'] ?? null;
                                }
                            } catch (\Exception $e) {
                                Log::error('Error obteniendo appointment después de reintentos', [
                                    'appointment_id' => $appointmentId,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                        
                        // Extraer product_id del primer line_item
                        $product_id = null;
                        if (!empty($detailsData->line_items)) {
                            $first_item = $detailsData->line_items[0];
                            $product_id = $first_item->product_id ?? null;
                        }
                        
                        // Guardar pedido en nuestra BD usando el mismo enfoque que syncWebhook
                        try {
                            // Preparar datos completos como en el controller
                            $completeData = [
                                'success' => true,
                                'data' => $detailsData,
                                'order_notes' => $orderNotes
                            ];
                            
                            // Crear orden directamente como en el controller
                            $orderModel = Order::updateOrCreate(
                                ['woocommerce_order_id' => $order['id']],
                                [
                                    'product_id' => $product_id,
                                    'status' => $detailsData->status,
                                    'customer_name' => $detailsData->billing->first_name ?? null,
                                    'customer_email' => $detailsData->billing->email ?? null,
                                    'total' => $detailsData->total ?? 0,
                                    'booking_start' => $bookingStart ? Carbon::parse($bookingStart) : null,
                                    'booking_end' => $bookingEnd ? Carbon::parse($bookingEnd) : null,
                                    'data' => $completeData,
                                    'created_at' => Carbon::parse($detailsData->date_created),
                                    'updated_at' => Carbon::parse($detailsData->date_modified),
                                ]
                            );
                            
                            $pageImported++;
                            $totalImported++;
                            $this->line("✓ Imported order #{$order['id']}");
                            
                        } catch (\Exception $e) {
                            Log::error('Error creando orden', [
                                'error' => $e->getMessage(),
                                'order_id' => $order['id']
                            ]);
                            
                            $pageErrors++;
                            $totalErrors++;
                            $this->error("✗ Error importing order #{$order['id']}: " . $e->getMessage());
                        }
                        
                    } catch (\Exception $e) {
                        $pageErrors++;
                        $totalErrors++;
                        $this->error("✗ Error importing order #{$order['id']}: " . $e->getMessage());
                        Log::error('Error importing WooCommerce order in command', [
                            'order_id' => $order['id'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                $this->info("Page {$page} completed: {$pageImported} imported, {$pageErrors} errors");
                $this->info("Total so far: {$totalImported} imported, {$totalErrors} errors");
                
                $page++;
                
                // Pequeña pausa entre páginas para no sobrecargar la API
                if (count($orders) === $perPage) {
                    $this->info("Waiting 1 second before next page...");
                    sleep(1);
                }
                
            } catch (\Exception $e) {
                $this->error("Failed to process page {$page}: " . $e->getMessage());
                $totalErrors++;
                
                // Si falla una página, intentar la siguiente
                $page++;
                continue;
            }
            
        } while (count($orders) === $perPage); // Continuar mientras obtenga el máximo de órdenes por página
        
        $this->info("\n=== Final Results ===");
        $this->info("Total pages processed: " . ($page - 1));
        $this->info("✓ Total imported: {$totalImported} orders");
        if ($totalErrors > 0) {
            $this->error("✗ Total errors: {$totalErrors} orders");
        }
        
        return $totalImported;
    }

    /**
     * Obtener una página específica de órdenes
     */
    private function getOrdersPage($page, $perPage, $status, $maxRetries = 3)
    {
        $url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc/v3/orders";
        
        $params = [
            'per_page' => $perPage,
            'page' => $page,
            'orderby' => 'date',
            'order' => 'desc'
        ];
        
        // Solo agregar status si no es 'any'
        if ($status !== 'any') {
            $params['status'] = $status;
        }
        
        $queryString = http_build_query($params);
        $fullUrl = $url . '?' . $queryString;
        
        return $this->makeRequestWithRetry($fullUrl, $maxRetries);
    }

    /**
     * Obtener TODAS las órdenes desde WooCommerce API usando paginación
     */
    private function getAllWooOrders($status, $maxRetries = 3)
    {
        $this->info('Fetching ALL orders from WooCommerce (this may take a while)...');
        
        $allOrders = [];
        $page = 1;
        $perPage = 100;
        
        do {
            $url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc/v3/orders";
            
            $params = [
                'per_page' => $perPage,
                'page' => $page,
                'orderby' => 'date',
                'order' => 'desc'
            ];
            
            // Solo agregar status si no es 'any'
            if ($status !== 'any') {
                $params['status'] = $status;
            }
            
            $queryString = http_build_query($params);
            $fullUrl = $url . '?' . $queryString;
            
            try {
                $orders = $this->makeRequestWithRetry($fullUrl, $maxRetries);
                
                if (empty($orders)) {
                    break; // No more orders
                }
                
                $allOrders = array_merge($allOrders, $orders);
                
                $this->info("Fetched page {$page}: " . count($orders) . " orders (Total: " . count($allOrders) . ")");
                
                $page++;
                
                // Pequeña pausa para no sobrecargar la API
                usleep(100000); // 0.1 segundos
                
            } catch (\Exception $e) {
                $this->error("Failed to fetch page {$page} after {$maxRetries} retries: " . $e->getMessage());
                break;
            }
            
        } while (count($orders) === $perPage); // Continuar mientras obtenga el máximo de órdenes por página
        
        $this->info("Total orders fetched: " . count($allOrders));
        
        Log::info('All WooCommerce orders fetched', [
            'status' => $status,
            'total_count' => count($allOrders),
            'pages_fetched' => $page - 1
        ]);
        
        return $allOrders;
    }

    /**
     * Obtener detalles completos de una orden desde WooCommerce
     */
    private function getOrderDetails($woocommerce_order_id)
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

    /**
     * Hacer request con reintentos
     */
    private function makeRequestWithRetry($url, $maxRetries = 3)
    {
        $auth = 'Basic ' . base64_encode('ck_86c1fcde56db9be52be54ef80cb5fdcd73655934:cs_6d8fafab71cd34b6253257457cbcca4415955658');
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Requests::get($url, [
                    'Authorization' => $auth,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]);

                if ($response->success) {
                    return json_decode($response->body, true);
                }
                
                if ($attempt === $maxRetries) {
                    throw new \Exception('Failed to fetch data after ' . $maxRetries . ' attempts. Last response: ' . $response->body);
                }
                
                // Esperar antes del siguiente reintento (exponential backoff)
                $waitTime = min(pow(2, $attempt - 1), 10); // 1, 2, 4, 8, max 10 seconds
                $this->warn("Attempt {$attempt} failed, waiting {$waitTime}s before retry...");
                sleep($waitTime);
                
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    throw new \Exception('Failed to fetch data after ' . $maxRetries . ' attempts: ' . $e->getMessage());
                }
                
                $waitTime = min(pow(2, $attempt - 1), 10);
                $this->warn("Attempt {$attempt} failed ({$e->getMessage()}), waiting {$waitTime}s before retry...");
                sleep($waitTime);
            }
        }
        
        throw new \Exception('Failed to fetch data after ' . $maxRetries . ' attempts');
    }

    /**
     * Obtener detalles de orden con reintentos
     */
    private function getOrderDetailsWithRetry($woocommerce_order_id, $maxRetries = 3)
    {
        $url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc/v3/orders/{$woocommerce_order_id}";

        $auth = 'Basic ' . base64_encode('ck_86c1fcde56db9be52be54ef80cb5fdcd73655934:cs_6d8fafab71cd34b6253257457cbcca4415955658');
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Requests::get($url, [
                    'Authorization' => $auth,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]);

                if(!$response->success){
                    if ($attempt === $maxRetries) {
                        return response()->json(['success'=>false,'message'=>'No se pudo obtener datos de WooCommerce después de ' . $maxRetries . ' intentos']);
                    }
                    
                    $waitTime = min(pow(2, $attempt - 1), 10);
                    $this->warn("Order details attempt {$attempt} failed, waiting {$waitTime}s before retry...");
                    sleep($waitTime);
                    continue;
                }

                $body = json_decode($response->body, true);

                // Obtener las order notes del pedido
                $notes_url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc/v3/orders/{$woocommerce_order_id}/notes";
                
                $notes_response = Requests::get($notes_url, [
                    'Authorization' => $auth,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
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
                
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    return response()->json(['success'=>false,'message'=>'Error después de ' . $maxRetries . ' intentos: ' . $e->getMessage()]);
                }
                
                $waitTime = min(pow(2, $attempt - 1), 10);
                $this->warn("Order details attempt {$attempt} failed ({$e->getMessage()}), waiting {$waitTime}s before retry...");
                sleep($waitTime);
            }
        }
        
        return response()->json(['success'=>false,'message'=>'Error después de ' . $maxRetries . ' intentos']);
    }

    /**
     * Obtener detalles de appointment con reintentos
     */
    private function getAppointmentDetailsWithRetry($appointmentId, $maxRetries = 3)
    {
        $url = "https://dev.puntacanadinnerintheskyrd.com/wp-json/wc-appointments/v1/appointments/{$appointmentId}";
        $auth = 'Basic ' . base64_encode('ck_86c1fcde56db9be52be54ef80cb5fdcd73655934:cs_6d8fafab71cd34b6253257457cbcca4415955658');
        
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Requests::get($url, [
                    'Authorization' => $auth,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]);

                if ($response->success) {
                    return json_decode($response->body, true);
                }
                
                if ($attempt === $maxRetries) {
                    return null;
                }
                
                $waitTime = min(pow(2, $attempt - 1), 10);
                $this->warn("Appointment attempt {$attempt} failed, waiting {$waitTime}s before retry...");
                sleep($waitTime);
                
            } catch (\Exception $e) {
                if ($attempt === $maxRetries) {
                    return null;
                }
                
                $waitTime = min(pow(2, $attempt - 1), 10);
                $this->warn("Appointment attempt {$attempt} failed, waiting {$waitTime}s before retry...");
                sleep($waitTime);
            }
        }
        
        return null;
    }
}
