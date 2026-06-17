<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductTimeslot;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class ManageProducts extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Reservas';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.productos';

    public ?int $selectedProductId = null;   // Product->id

    public string $activeTab = 'map';        // map | config

    public string $selectedDate;

    /** Hora (slot) del vuelo mostrado en el mapa */
    public ?string $selectedHour = null;

    /** Asiento seleccionado para popover: índice 0..21 o null */
    public ?int $seatPopup = null;

    /** Horarios por defecto editables del producto seleccionado */
    public array $slots = [];

    public const WEEKDAYS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

    public function getTitle(): string
    {
        return 'Reservas e Inventario';
    }

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedProductId = Product::orderBy('id')->first()?->id;
        $this->loadSlots();
    }

    public function selectProduct(int $id): void
    {
        $this->selectedProductId = $id;
        $this->seatPopup = null;
        $this->selectedHour = null;
        $this->loadSlots();
    }

    public function selectSlot(string $hour): void
    {
        $this->selectedHour = $hour;
        $this->seatPopup = null;
    }

    public function selectSeat(?int $i): void
    {
        $this->seatPopup = ($this->seatPopup === $i) ? null : $i;
    }

    public function getCurrentFlight(): ?array
    {
        $flights = $this->getFlights();
        if (empty($flights)) {
            return null;
        }
        foreach ($flights as $f) {
            if ($f['hour'] === $this->selectedHour) {
                return $f;
            }
        }

        return $flights[0];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function changeDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->seatPopup = null;
    }

    public function shiftDate(int $days): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDays($days)->format('Y-m-d');
        $this->seatPopup = null;
    }

    public function getProduct(): ?Product
    {
        return $this->selectedProductId ? Product::find($this->selectedProductId) : null;
    }

    public function priceOf(?Product $p): int
    {
        return VentasPage::PRICES[$p?->name] ?? 120;
    }

    /* ======================= catálogo ======================= */

    public function getCatalog(): array
    {
        return Product::orderBy('id')->get()->map(function (Product $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $this->priceOf($p),
                'capacity' => (int) ($p->default_capacity ?: 22),
                'active' => $p->timeslots()->where('active', true)->exists(),
                'night' => Str::contains(Str::lower($p->name), ['night', 'surf', 'level', 'bar', 'lounge']),
            ];
        })->toArray();
    }

    /* ======================= mapa de la reserva ======================= */

    public function paxOf(Order $order): int
    {
        $data = json_decode(json_encode($order->data), true);
        $items = $data['data']['line_items'] ?? [];
        $pax = 0;
        foreach ($items as $item) {
            foreach ($item['meta_data'] ?? [] as $meta) {
                if (($meta['key'] ?? null) === '_pao_ids') {
                    foreach ($meta['value'] ?? [] as $v) {
                        if (($v['key'] ?? null) === 'Quantity') {
                            $pax += (int) ($v['value'] ?? 0);
                        }
                    }
                }
            }
        }
        if ($pax === 0) {
            foreach ($items as $item) {
                $pax += (int) ($item['quantity'] ?? 0);
            }
        }

        return max($pax, 1);
    }

    protected function cleanName(?string $name): string
    {
        return trim(preg_replace('/\s*\b(viator|ota|hotel)\b\s*/i', ' ', $name ?? '')) ?: 'Cliente';
    }

    /** Vuelos (slots) del producto seleccionado en la fecha. */
    public function getFlights(): array
    {
        $product = $this->getProduct();
        if (! $product) {
            return [];
        }

        $cap = (int) ($product->default_capacity ?: 22);
        $price = $this->priceOf($product);

        $orders = Order::where('product_id', $product->wordpress_product_id)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->whereDate('booking_start', $this->selectedDate)
            ->orderBy('booking_start')
            ->get();

        $flights = [];
        foreach ($orders->groupBy(fn ($o) => Carbon::parse($o->booking_start)->format('H:i')) as $hour => $group) {
            $seats = array_fill(0, $cap, null);
            $idx = 0;
            $sold = 0;
            $unpaid = 0;
            $revenue = 0;
            $pending = 0;

            foreach ($group as $order) {
                $pax = $this->paxOf($order);
                $paid = in_array($order->status, ['completed', 'processing'], true);
                $sold += $pax;
                $revenue += (float) $order->total;
                if (! $paid) {
                    $unpaid += $pax;
                    $pending += (float) $order->total;
                }
                for ($k = 0; $k < $pax && $idx < $cap; $k++) {
                    $seats[$idx++] = [
                        'name' => $this->cleanName($order->customer_name),
                        'paid' => $paid,
                        'channel' => Str::contains(Str::lower($order->customer_name ?? ''), ['viator', 'ota']) ? 'viator' : 'web',
                        'order_id' => $order->id,
                    ];
                }
            }

            $flights[] = [
                'hour' => $hour,
                'name' => $product->name,
                'cap' => $cap,
                'sold' => min($sold, $cap),
                'unpaid' => $unpaid,
                'revenue' => $revenue,
                'pending' => $pending,
                'min' => 8,
                'seats' => $seats,
                'price' => $price,
                'empty' => false,
            ];
        }

        // Sin reservas ese día: mostramos la plataforma vacía igual, a partir de los
        // horarios configurados para ese día (o uno por defecto) para poder operar/vender.
        if (empty($flights)) {
            $flights = $this->emptyFlights($product, $cap, $price);
        }

        return $flights;
    }

    /** Plataformas vacías (todos los asientos libres) para una fecha sin reservas. */
    protected function emptyFlights(Product $product, int $cap, int $price): array
    {
        $weekday = Carbon::parse($this->selectedDate)->dayOfWeek; // 0=Domingo … 6=Sábado

        $hours = $product->timeslots()
            ->where('active', true)
            ->where('weekday', $weekday)
            ->orderBy('start_time')
            ->pluck('start_time')
            ->map(fn ($t) => substr((string) $t, 0, 5))
            ->unique()
            ->values()
            ->all();

        // Si no hay horario configurado para ese día, mostramos una plataforma genérica.
        if (empty($hours)) {
            $hours = ['—'];
        }

        return array_map(fn ($hour) => [
            'hour' => $hour,
            'name' => $product->name,
            'cap' => $cap,
            'sold' => 0,
            'unpaid' => 0,
            'revenue' => 0,
            'pending' => 0,
            'min' => 8,
            'seats' => array_fill(0, $cap, null),
            'price' => $price,
            'empty' => true,
        ], $hours);
    }

    /* ======================= configuración de horarios ======================= */

    protected function loadSlots(): void
    {
        $product = $this->getProduct();
        $this->slots = [];
        if ($product) {
            foreach ($product->timeslots()->orderBy('weekday')->orderBy('start_time')->get() as $t) {
                $this->slots[] = [
                    'id' => $t->id,
                    'weekday' => (int) $t->weekday,
                    'start_time' => substr((string) $t->start_time, 0, 5),
                    'capacity' => (int) ($product->default_capacity ?: 22),
                    'active' => (bool) $t->active,
                ];
            }
        }
    }

    public function addSlot(): void
    {
        $this->slots[] = ['id' => null, 'weekday' => 6, 'start_time' => '17:30', 'capacity' => 22, 'active' => true];
    }

    public function removeSlot(int $i): void
    {
        unset($this->slots[$i]);
        $this->slots = array_values($this->slots);
    }

    public function toggleSlot(int $i): void
    {
        if (isset($this->slots[$i])) {
            $this->slots[$i]['active'] = ! $this->slots[$i]['active'];
        }
    }

    public function saveSlots(): void
    {
        $product = $this->getProduct();
        if (! $product) {
            return;
        }

        $product->timeslots()->delete();
        foreach ($this->slots as $slot) {
            if (! empty($slot['start_time'])) {
                ProductTimeslot::create([
                    'product_id' => $product->id,
                    'weekday' => (int) ($slot['weekday'] ?? 6),
                    'start_time' => $slot['start_time'],
                    'priority' => 10,
                    'active' => (bool) ($slot['active'] ?? true),
                ]);
            }
        }

        $this->loadSlots();

        Notification::make()->success()->title('Horarios guardados ✓')
            ->body('La capacidad y los horarios se aplican a escritorio, cocina y web.')->send();
    }
}
