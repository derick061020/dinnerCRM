<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductTimeslot;
use App\Models\Inventory;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions;
use Filament\Notifications\Notification;

class ManageProducts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $title = 'Administración de Productos';
    protected static string $view = 'filament.pages.productos';

    public $selectedItem = null;
    public $activeTab = 'reservations';
    public $configurationTab = 'default';
    public $selectedDate = null;
    public $showTableView = false;
    public $currentCustomSchedule = null;
    public $showSeatInfo = null;

    // Productos desde WooCommerce
    public $testItems = [];
    public $selectedItemData = null;

    // Horarios
    public $itemSchedules = [
        'default' => [],
        'custom' => []
    ];

    // Estado de la mesa
    public $tableSeats = [];

    public function mount(): void
    {
        $this->loadProducts();
        $this->initializeTableSeats();
        $this->loadDefaultSchedules();
        // No cargar horarios personalizados aquí porque no hay fecha seleccionada inicialmente
    }

    protected function loadProducts(): void
    {
        // Cargar productos desde la base de datos
        $products = Product::with('timeslots')->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price ?? 0,
                'image' => $product->image_url ?? null,
                'categories' => $product->category ?? 'Sin categoría',
                'type' => 'appointment', // Todos son de reserva
                'wordpress_product_id' => $product->wordpress_product_id
            ];
        })->toArray();

        $this->testItems = $products;
    }

    protected function initializeTableSeats(): void
    {
        // Inicializar 35 asientos (para la vista móvil)
        $this->tableSeats = [];
        
        // Usar la fecha seleccionada o hoy por defecto
        $targetDate = $this->selectedDate ? Carbon::parse($this->selectedDate) : Carbon::today();
        
        // Cargar órdenes por fecha de booking (no created_at)
        $ordersForDate = Order::with('product')
            ->where(function($query) use ($targetDate) {
                // Buscar órdenes que tengan booking_start o booking_end en la fecha seleccionada
                $query->whereDate('booking_start', $targetDate)
                      ->orWhereDate('booking_end', $targetDate);
            })
            ->where('status', '!=', 'cancelled')
            ->get();

        // Distribuir órdenes en los asientos
        $seatNumber = 1;
        foreach ($ordersForDate as $order) {
            if ($seatNumber <= 35) {
                // Usar la hora de booking_start o created_at como fallback
                $bookingTime = $order->booking_start ? Carbon::parse($order->booking_start) : $order->created_at;
                
                $this->tableSeats[$seatNumber] = [
                    'occupied' => true,
                    'customer' => [
                        'name' => $order->customer_name ?? 'Cliente',
                        'time' => $bookingTime->format('H:i'),
                        'phone' => 'N/A',
                        'email' => $order->customer_email ?? 'N/A',
                        'booking_start' => $order->booking_start,
                        'booking_end' => $order->booking_end
                    ],
                    'order_id' => $order->id
                ];
                $seatNumber++;
            }
        }

        // Llenar los asientos restantes como vacíos
        for ($i = 1; $i <= 35; $i++) {
            if (!isset($this->tableSeats[$i])) {
                $this->tableSeats[$i] = [
                    'occupied' => false,
                    'customer' => null
                ];
            }
        }
    }

    protected function loadDefaultSchedules(): void
    {
        // Cargar horarios por defecto desde la base de datos
        if ($this->selectedItem) {
            $product = Product::find($this->selectedItem);
            if ($product) {
                $timeslots = $product->timeslots()->where('active', true)->orderBy('weekday')->orderBy('priority')->get();
                $this->itemSchedules['default'] = $timeslots->map(function ($slot) {
                    return [
                        'id' => $slot->id,
                        'weekday' => $slot->weekday,
                        'start_time' => $slot->start_time,
                        'priority' => $slot->priority
                    ];
                })->toArray();
            }
        }
    }

    public function selectItem($itemId): void
    {
        $this->selectedItem = $itemId;
        
        if ($itemId) {
            $this->selectedItemData = collect($this->testItems)->firstWhere('id', $itemId);
            $this->loadDefaultSchedules();
            // Solo cargar horarios personalizados si ya hay una fecha seleccionada
            if ($this->selectedDate) {
                $this->loadCustomSchedules();
            }
        } else {
            $this->selectedItemData = null;
            $this->itemSchedules = ['default' => [], 'custom' => []];
            $this->currentCustomSchedule = null;
        }
    }

    protected function loadCustomSchedules(): void
    {
        // Cargar horarios personalizados por fecha desde Inventory
        $this->itemSchedules['custom'] = [];
        
        if ($this->selectedDate && $this->selectedItem) {
            // Debug: log para ver qué se está buscando
            \Log::info("Loading custom schedules", [
                'selectedDate' => $this->selectedDate,
                'selectedItem' => $this->selectedItem
            ]);
            
            // Convertir la fecha a formato Y-m-d para asegurar consistencia
            $date = Carbon::parse($this->selectedDate)->format('Y-m-d');
            
            $inventories = Inventory::where('product_id', $this->selectedItem)
                ->whereDate('date', $date)  // Usar whereDate para comparar solo la fecha
                ->orderBy('start_time')
                ->get();
            
            \Log::info("Found inventories", ['count' => $inventories->count(), 'searching_date' => $date]);
            
            $timeSlots = $inventories->map(function ($inventory) {
                return [
                    'id' => $inventory->id,
                    'start_time' => $inventory->start_time,
                    'capacity_total' => $inventory->capacity_total,
                    'capacity_used' => $inventory->capacity_used
                ];
            })->toArray();
            
            $this->currentCustomSchedule = [
                'date' => $this->selectedDate,
                'timeSlots' => $timeSlots
            ];
            
            \Log::info("CurrentCustomSchedule set", [
                'date' => $this->currentCustomSchedule['date'],
                'timeSlots_count' => count($this->currentCustomSchedule['timeSlots'])
            ]);
        } else {
            // Si no hay fecha o producto seleccionado, limpiar currentCustomSchedule
            $this->currentCustomSchedule = null;
            \Log::info("No date or item selected, clearing currentCustomSchedule");
        }
    }

    public function setActiveTab($tab): void
    {
        $this->activeTab = $tab;
    }

    public function setConfigurationTab($tab): void
    {
        $this->configurationTab = $tab;
    }

    public function updatedSelectedDate($value): void
    {
        // Este método se llama automáticamente cuando selectedDate cambia
        if ($this->selectedItem) {
            $this->loadCustomSchedules();
        }
    }

    public function addDefaultTimeSlot(): void
    {
        // Agregar un nuevo horario con weekday por defecto (1 = lunes)
        $newSlot = [
            'id' => null,
            'weekday' => 1,
            'start_time' => '10:00',
            'priority' => 10
        ];
        $this->itemSchedules['default'][] = $newSlot;
    }

    public function removeDefaultTimeSlot($index): void
    {
        unset($this->itemSchedules['default'][$index]);
        $this->itemSchedules['default'] = array_values($this->itemSchedules['default']);
    }

    public function addCustomTimeSlot(): void
    {
        if (!$this->currentCustomSchedule) {
            $this->currentCustomSchedule = [
                'date' => $this->selectedDate,
                'timeSlots' => []
            ];
        }
        
        $newSlot = [
            'id' => null,
            'start_time' => '10:00',
            'capacity_total' => 21,
            'capacity_used' => 0
        ];
        
        $this->currentCustomSchedule['timeSlots'][] = $newSlot;
    }

    public function removeCustomTimeSlot($index): void
    {
        if ($this->currentCustomSchedule && isset($this->currentCustomSchedule['timeSlots'])) {
            unset($this->currentCustomSchedule['timeSlots'][$index]);
            $this->currentCustomSchedule['timeSlots'] = array_values($this->currentCustomSchedule['timeSlots']);
        }
    }

    protected function updateCustomSchedule(): void
    {
        $customIndex = array_search($this->selectedDate, array_column($this->itemSchedules['custom'], 'date'));
        if ($customIndex !== false) {
            $this->itemSchedules['custom'][$customIndex] = $this->currentCustomSchedule;
        }
    }


    public function toggleTableView(): void
    {
        $this->showTableView = !$this->showTableView;
    }

    public function goToCustomer($seatNumber): void
    {
        // Convert string parameter to integer if needed
        $seatNumber = (int)$seatNumber;
        
        if (isset($this->tableSeats[$seatNumber]) && $this->tableSeats[$seatNumber]['occupied']) {
            $orderId = $this->tableSeats[$seatNumber]['order_id'];
            // Redirigir a la página de detalles de la orden
            $this->redirect('/orders/' . $orderId);
        }
    }

    public function changeDate($newDate): void
    {
        // Con type="date" el navegador envía Y-m-d directamente
        $this->selectedDate = $newDate;
        $this->initializeTableSeats(); // Recargar las mesas para la nueva fecha
        
        Notification::make()
            ->success()
            ->title('Fecha actualizada')
            ->body('Mostrando reservas para: ' . ($this->selectedDate ? Carbon::parse($this->selectedDate)->format('d/m/Y') : 'Hoy'))
            ->send();
    }

    public function refreshTables(): void
    {
        $this->initializeTableSeats();
        
        Notification::make()
            ->info()
            ->title('Mesas actualizadas')
            ->body('La ocupación de las mesas ha sido recargada')
            ->send();
    }

    public function toggleSeatInfo($seatNumber): void
    {
        // Handle the 'null' string case properly
        if ($seatNumber === 'null' || $seatNumber === null) {
            $this->showSeatInfo = null;
            return;
        }
        
        // Convert string parameter to integer
        $seatNumber = (int)$seatNumber;
        
        // Toggle seat info visibility
        if ($this->showSeatInfo === $seatNumber) {
            $this->showSeatInfo = null;
        } else {
            $this->showSeatInfo = $seatNumber;
        }
    }

    public function saveSchedules(): void
    {
        if (!$this->selectedItem) {
            return;
        }

        $product = Product::find($this->selectedItem);
        if (!$product) {
            return;
        }

        // Guardar horarios por defecto en ProductTimeslot
        $product->timeslots()->delete();
        foreach ($this->itemSchedules['default'] as $timeSlot) {
            if (!empty($timeSlot['start_time'])) {
                ProductTimeslot::create([
                    'product_id' => $product->id,
                    'weekday' => $timeSlot['weekday'] ?? 1,
                    'start_time' => $timeSlot['start_time'],
                    'priority' => $timeSlot['priority'] ?? 10,
                    'active' => true
                ]);
            }
        }

        // Guardar horarios personalizados en Inventory
        if ($this->currentCustomSchedule && !empty($this->selectedDate)) {
            // Convertir la fecha a formato Y-m-d para asegurar consistencia
            $date = Carbon::parse($this->selectedDate)->format('Y-m-d');
            
            // Eliminar inventarios existentes para esta fecha y producto
            Inventory::where('product_id', $product->id)
                ->whereDate('date', $date)
                ->delete();
            
            // Crear nuevos inventarios para los horarios personalizados
            foreach ($this->currentCustomSchedule['timeSlots'] ?? [] as $timeSlot) {
                if (!empty($timeSlot['start_time'])) {
                    Inventory::create([
                        'product_id' => $product->id,
                        'date' => $date,
                        'start_time' => $timeSlot['start_time'],
                        'capacity_total' => $timeSlot['capacity_total'] ?? $product->default_capacity ?? 21,
                        'capacity_used' => 0
                    ]);
                }
            }
        }

        Notification::make()
            ->success()
            ->title('Horarios guardados correctamente')
            ->send();
    }

    public function getStatistics(): array
    {
        $today = Carbon::today();
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $pendingOrders = Order::where('status', 'pending')->count();

        return [
            'today_orders' => $ordersToday,
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'pending_orders' => $pendingOrders,
            'completion_rate' => $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 1) : 0
        ];
    }

    protected function getViewData(): array
    {
        return [
            'statistics' => $this->getStatistics(),
            'recentOrders' => Order::with('product')
                ->latest()
                ->take(5)
                ->get()
        ];
    }
}
