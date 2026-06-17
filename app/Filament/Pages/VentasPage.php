<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\WithPagination;

class VentasPage extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $navigationGroup = 'Comercial';

    protected static string $view = 'filament.pages.ventas-page';

    protected static ?int $navigationSort = 1;

    /** Precio base por pax según experiencia (los productos no tienen columna price). */
    public const PRICES = [
        'SkyBrunch' => 99,
        'SURF AND TURF' => 139,
        'Level Up Experience' => 185,
        'SkyBar Lounge' => 150,
        'Chef Selection Experience' => 116,
    ];

    public const PACKS = [
        'exclusive' => ['name' => 'Exclusive Pack', 'price' => 69, 'desc' => 'Flores · transporte VIP · postre · fotos'],
        'anniversary' => ['name' => 'Anniversary Pack', 'price' => 79, 'desc' => 'Brindis show shot · souvenir · fotos'],
        'birthday' => ['name' => 'Birthday Pack', 'price' => 99, 'desc' => 'Pastel · transporte VIP · fotos · show shot'],
    ];

    /** Pantalla activa: list | detail | create */
    public string $screen = 'list';

    public string $search = '';
    public string $filter = 'all';

    /** Mes seleccionado en formato Y-m. Vacío = todos los meses. */
    public string $month = '';

    public ?int $selectedId = null;

    /** Datos del modal "vista rápida" */
    public ?int $quickId = null;

    /** Reagendar */
    public bool $showReschedule = false;
    public ?string $rescheduleDate = null;

    /** Cancelar */
    public bool $showCancel = false;
    public string $cancelReason = 'Solicitud del cliente';

    /* ---- formulario crear venta ---- */
    public ?int $cProductId = null;
    public string $cDate;
    public string $cTime = '20:00';
    public int $cPax = 2;
    public array $cGuests = [];
    public array $cPacks = [];
    public string $cHotel = '';
    public string $cOccasion = 'Ninguna';
    public string $cName = '';
    public string $cPhone = '';
    public string $cEmail = '';
    public string $cChannel = 'WhatsApp directo';
    public string $cMode = 'full';
    public float $cDiscount = 0;
    public string $cNote = '';

    protected $queryString = [
        'screen' => ['except' => 'list'],
        'filter' => ['except' => 'all'],
        'month' => ['except' => ''],
        'selectedId' => ['except' => null],
    ];

    public function getTitle(): string
    {
        return 'Ventas';
    }

    public function mount(): void
    {
        $this->cDate = now()->format('Y-m-d');
        $this->cProductId = Product::first()?->wordpress_product_id;
        $this->syncGuests();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $f): void
    {
        $this->filter = $f;
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function stepMonth(int $d): void
    {
        $base = $this->month !== '' ? $this->parseMonth() : Carbon::now()->startOfMonth();
        $this->month = $base->addMonths($d)->format('Y-m');
        $this->resetPage();
    }

    public function setMonth(int $year, int $month): void
    {
        $this->month = sprintf('%04d-%02d', $year, max(1, min(12, $month)));
        $this->resetPage();
    }

    public function clearMonth(): void
    {
        $this->month = '';
        $this->resetPage();
    }

    /** Parsea el mes seleccionado al inicio de mes (evita desbordes en meses cortos). */
    protected function parseMonth(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->month . '-01')->startOfMonth();
    }

    /** Etiqueta legible del mes seleccionado (ej: "Junio 2026"). */
    public function monthLabel(): string
    {
        if ($this->month === '') {
            return 'Todos los meses';
        }

        return Str::ucfirst($this->parseMonth()->isoFormat('MMMM YYYY'));
    }

    /** Aplica el filtro mensual por fecha de pago (date_paid), con fallback a created_at si no pagó. */
    protected function applyMonth($q)
    {
        if ($this->month !== '') {
            $date = $this->parseMonth();
            $q->whereRaw('YEAR(COALESCE(date_paid, created_at)) = ? AND MONTH(COALESCE(date_paid, created_at)) = ?', [$date->year, $date->month]);
        }

        return $q;
    }

    public function go(string $screen, ?int $id = null): void
    {
        $this->screen = $screen;
        $this->selectedId = $id;
        $this->quickId = null;
        $this->showReschedule = false;
        $this->showCancel = false;
    }

    /* ======================= derivaciones ======================= */

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

    /**
     * ¿Es un complemento de transporte (traslado, pickup…)? No es plato ni extra de mesa.
     */
    public function isAddon(string $name): bool
    {
        return Str::contains(Str::lower($name), [
            'transport', 'round trip', 'roundtrip', 'transfer', 'traslado',
            'pickup', 'pick up', 'shuttle', 'passenger', 'pasajero', 'recogida',
        ]);
    }

    /** ¿Es un extra de ocasión (pastel, pack, champagne…)? No es un plato principal de un comensal. */
    public function isExtra(string $name): bool
    {
        return Str::contains(Str::lower($name), [
            'cake', 'pastel', 'birthday', 'cumpleaños', 'anniversary', 'aniversario',
            'pack', 'champagne', 'champán', 'champan', 'bottle', 'botella', 'wine', 'vino',
            'souvenir', 'photo', 'foto', 'upgrade', 'brindis', 'toast',
        ]);
    }

    /** Items de comida de una orden, clasificados. @return array{mains:array,extras:array,addons:array} */
    public function menuItemsOf(Order $order): array
    {
        $data = json_decode(json_encode($order->data), true);
        $items = $data['data']['line_items'] ?? [];
        $mains = [];
        $extras = [];
        $addons = [];

        foreach ($items as $item) {
            foreach ($item['meta_data'] ?? [] as $meta) {
                if (($meta['key'] ?? null) === '_pao_ids' && isset($meta['value'])) {
                    foreach ($meta['value'] as $pao) {
                        $key = $pao['key'] ?? null;
                        if ($key === null || $key === 'Quantity') {
                            continue;
                        }
                        $entry = ['name' => $key, 'qty' => (int) ($pao['value'] ?? 1)];
                        if ($this->isAddon($key)) {
                            $addons[] = $entry;
                        } elseif ($this->isExtra($key)) {
                            $extras[] = $entry;
                        } else {
                            $mains[] = $entry;
                        }
                    }
                }
            }
        }

        return ['mains' => $mains, 'extras' => $extras, 'addons' => $addons];
    }

    /** Solo platos principales (para el popup de menús). */
    public function dishesOf(Order $order): array
    {
        return $this->menuItemsOf($order)['mains'];
    }

    /** Extras de ocasión (pastel, pack…). */
    public function extrasOf(Order $order): array
    {
        return $this->menuItemsOf($order)['extras'];
    }

    /** Complementos de transporte. */
    public function addOnsOf(Order $order): array
    {
        return $this->menuItemsOf($order)['addons'];
    }

    /**
     * Distribuye TODOS los platos principales entre los comensales (longitud = pax).
     * Cada unidad de un plato se reparte round-robin: si un plato tiene qty = pax,
     * todos los comensales lo reciben (combos tipo Surf & Turf); si son elecciones
     * distintas (1+1), cada comensal recibe la suya.
     *
     * @return array<int, array<int, string>> índice de comensal => lista de platos
     */
    public function comensalesOf(Order $order): array
    {
        $pax = $this->paxOf($order);
        $comensales = array_fill(0, $pax, []);

        $ptr = 0;
        foreach ($this->dishesOf($order) as $d) {
            $units = max(1, $d['qty']);
            for ($u = 0; $u < $units; $u++) {
                $comensales[$ptr % $pax][] = $d['name'];
                $ptr++;
            }
        }

        return $comensales;
    }

    public function phoneOf(Order $order): ?string
    {
        $data = json_decode(json_encode($order->data), true);
        $billing = $data['data']['billing'] ?? [];

        return $billing['phone'] ?? null;
    }

    public function hotelOf(Order $order): string
    {
        $data = json_decode(json_encode($order->data), true);
        $billing = $data['data']['billing'] ?? [];
        $addr = trim(($billing['address_1'] ?? '') . ' ' . ($billing['city'] ?? ''));

        return $addr !== '' ? $addr : 'Pickup propio';
    }

    public function channelOf(Order $order): string
    {
        $haystack = strtolower(trim(($order->origin ?? '') . ' ' . ($order->customer_name ?? '')));
        if (Str::contains($haystack, ['viator', 'ota'])) {
            return 'viator';
        }
        if (Str::contains($haystack, 'hotel')) {
            return 'hotel';
        }
        if (Str::contains($haystack, ['instagram', ' ig'])) {
            return 'ig';
        }

        return 'web';
    }

    public function cleanName(?string $name): string
    {
        return trim(preg_replace('/\s*\b(viator|ota|hotel)\b\s*/i', ' ', $name ?? '')) ?: 'Sin nombre';
    }

    public function slotLabel(?Carbon $date): string
    {
        if (! $date) {
            return '';
        }

        return match (true) {
            $date->hour < 12 => 'Brunch',
            $date->hour < 16 => 'Lunch',
            $date->hour < 19 => 'Sunset',
            $date->hour < 21 => 'Night',
            default => 'Level Up',
        };
    }

    /** [paymentStatus, flightStatus] derivados. */
    public function statusOf(Order $order): array
    {
        $paid = in_array($order->status, ['completed', 'processing'], true);
        $pay = $paid ? 'paid' : 'pend';

        if (in_array($order->status, ['cancelled', 'failed'], true)) {
            $st = 'can';
        } elseif (! $order->booking_start) {
            $st = 'proc';
        } elseif ($paid && $order->booking_start->isPast()) {
            $st = 'vol';
        } elseif ($paid) {
            $st = 'conf';
        } else {
            $st = 'proc';
        }

        return [$pay, $st];
    }

    /* ======================= listado ======================= */

    protected function baseQuery()
    {
        return Order::query()->with('product');
    }

    public function getOrders(): LengthAwarePaginator
    {
        $q = $this->baseQuery();

        $today = Carbon::today();
        $now = Carbon::now();

        match ($this->filter) {
            'conf' => $q->whereIn('status', ['completed', 'processing'])->whereNotNull('booking_start'),
            'unpaid' => $q->whereIn('status', ['pending', 'a-la-espera']),
            // Coincide con la alerta "Pagos pendientes en reservas <72h" del Escritorio.
            'unpaid-soon' => $q->whereIn('status', ['pending', 'a-la-espera'])
                ->whereNotNull('booking_start')
                ->whereBetween('booking_start', [$now, $now->copy()->addHours(72)]),
            'nodate' => $q->whereNull('booking_start')->whereNotIn('status', ['cancelled', 'failed']),
            // Coincide con la alerta "Reservas pagadas sin fecha" del Escritorio.
            'nodate-paid' => $q->whereNull('booking_start')->whereIn('status', ['completed', 'processing']),
            'vol' => $q->where('status', 'completed')->whereDate('booking_start', '<', $today),
            'can' => $q->whereIn('status', ['cancelled', 'failed']),
            'today' => $q->whereDate('booking_start', $today),
            default => $q,
        };

        // El filtro mensual (por fecha de venta) no aplica a criterios "sin fecha",
        // a "unpaid-soon" (ventana fija de 72h) ni a "today" (día operativo absoluto:
        // una reserva de hoy pudo venderse en otro mes).
        if (! in_array($this->filter, ['nodate', 'nodate-paid', 'unpaid-soon', 'today'], true)) {
            $this->applyMonth($q);
        }

        if (trim($this->search) !== '') {
            $s = '%' . trim($this->search) . '%';
            $q->where(function ($w) use ($s) {
                $w->where('customer_name', 'like', $s)
                    ->orWhere('customer_email', 'like', $s)
                    ->orWhere('id', 'like', $s)
                    ->orWhere('woocommerce_order_id', 'like', $s);
            });
        }

        return $q->orderByRaw('booking_start IS NULL DESC')
            ->orderByDesc('booking_start')
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function getCounts(): array
    {
        $today = Carbon::today();

        return [
            'all' => $this->applyMonth(Order::query())->count(),
            'conf' => $this->applyMonth(Order::whereIn('status', ['completed', 'processing'])->whereNotNull('booking_start'))->count(),
            'unpaid' => $this->applyMonth(Order::whereIn('status', ['pending', 'a-la-espera']))->count(),
            'nodate' => Order::whereNull('booking_start')->whereNotIn('status', ['cancelled', 'failed'])->count(),
            'vol' => $this->applyMonth(Order::where('status', 'completed')->whereDate('booking_start', '<', $today))->count(),
            'can' => $this->applyMonth(Order::whereIn('status', ['cancelled', 'failed']))->count(),
            'today' => Order::whereDate('booking_start', $today)->count(),
            'periodTotal' => (float) $this->applyMonth(Order::query())->sum('total'),
        ];
    }

    public function getSelected(): ?Order
    {
        return $this->selectedId ? Order::with('product')->find($this->selectedId) : null;
    }

    public function getQuick(): ?Order
    {
        return $this->quickId ? Order::with('product')->find($this->quickId) : null;
    }

    public function showQuick(int $id): void
    {
        $this->quickId = $id;
    }

    public function closeQuick(): void
    {
        $this->quickId = null;
    }

    /* ======================= acciones detalle ======================= */

    public function resendConfirmation(): void
    {
        Notification::make()->success()->title('Confirmación reenviada ✓')
            ->body('Se reenvió la confirmación por email al cliente.')->send();
    }

    public function sendPaymentLink(): void
    {
        Notification::make()->success()->title('Link de pago enviado ✓')
            ->body('Se generó y envió el link de pago al cliente.')->send();
    }

    public function openReschedule(): void
    {
        $order = $this->getSelected();
        $this->rescheduleDate = $order?->booking_start?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->showReschedule = true;
    }

    public function reschedule(): void
    {
        $order = $this->getSelected();
        if ($order && $this->rescheduleDate) {
            $order->update(['booking_start' => Carbon::parse($this->rescheduleDate)]);
            $this->showReschedule = false;
            Notification::make()->success()->title('Reserva reagendada ✓')
                ->body('Nueva fecha: ' . $order->booking_start->format('d/m/Y H:i'))->send();
        }
    }

    public function openCancel(): void
    {
        $this->showCancel = true;
    }

    public function cancelSale(): void
    {
        $order = $this->getSelected();
        if ($order) {
            $data = $order->data ?? collect();
            $arr = json_decode(json_encode($data), true) ?: [];
            $arr['cancel_reason'] = $this->cancelReason;
            $arr['cancelled_at'] = now()->toIso8601String();
            $order->update(['status' => 'cancelled', 'data' => $arr]);
            $this->showCancel = false;
            Notification::make()->warning()->title('Venta cancelada')
                ->body('Motivo: ' . $this->cancelReason)->send();
        }
    }

    /* ======================= crear venta ======================= */

    public function updatedCPax(): void
    {
        $this->cPax = max(1, min(22, (int) $this->cPax));
        $this->syncGuests();
    }

    public function stepPax(int $d): void
    {
        $this->cPax = max(1, min(22, $this->cPax + $d));
        $this->syncGuests();
    }

    protected function syncGuests(): void
    {
        $guests = [];
        for ($i = 0; $i < $this->cPax; $i++) {
            $guests[$i] = $this->cGuests[$i] ?? ['name' => '', 'menu' => 'Carne', 'restriction' => ''];
        }
        $this->cGuests = $guests;
    }

    public function togglePack(string $key): void
    {
        if (in_array($key, $this->cPacks, true)) {
            $this->cPacks = array_values(array_diff($this->cPacks, [$key]));
        } else {
            $this->cPacks[] = $key;
        }
    }

    public function getCreateProduct(): ?Product
    {
        return Product::where('wordpress_product_id', $this->cProductId)->first();
    }

    public function getCreatePrice(): int
    {
        $p = $this->getCreateProduct();

        return self::PRICES[$p?->name] ?? 120;
    }

    public function getCreateTotal(): array
    {
        $price = $this->getCreatePrice();
        $base = $price * $this->cPax;
        $packTotal = 0;
        foreach ($this->cPacks as $k) {
            $packTotal += self::PACKS[$k]['price'] ?? 0;
        }
        $sub = $base + $packTotal;
        $disc = $sub * ($this->cDiscount / 100);
        $factor = $this->cMode === 'deposit' ? 0.5 : 1;
        $total = ($sub - $disc) * $factor;

        return compact('price', 'base', 'packTotal', 'sub', 'disc', 'total');
    }

    public function createSale(): void
    {
        $this->validate([
            'cName' => 'required|string|min:2',
            'cProductId' => 'required',
            'cDate' => 'required|date',
            'cTime' => 'required',
        ]);

        $product = $this->getCreateProduct();
        $totals = $this->getCreateTotal();
        $bookingStart = Carbon::parse($this->cDate . ' ' . $this->cTime);

        // Construir line_items estilo WooCommerce con _pao_ids para que cocina lo lea
        $paoValues = [['key' => 'Quantity', 'value' => $this->cPax]];
        foreach ($this->cGuests as $g) {
            $paoValues[] = ['key' => $g['menu'], 'value' => 1];
        }

        $data = [
            'data' => [
                'line_items' => [[
                    'name' => $product?->name ?? 'Experiencia',
                    'quantity' => $this->cPax,
                    'total' => (string) $totals['sub'],
                    'meta_data' => [['key' => '_pao_ids', 'value' => $paoValues]],
                ]],
                'billing' => [
                    'first_name' => $this->cName,
                    'phone' => $this->cPhone,
                    'email' => $this->cEmail,
                    'address_1' => $this->cHotel,
                ],
                'packs' => array_map(fn ($k) => self::PACKS[$k]['name'], $this->cPacks),
                'occasion' => $this->cOccasion,
                'channel' => $this->cChannel,
                'internal_note' => $this->cNote,
            ],
        ];

        Order::create([
            'woocommerce_order_id' => null, // venta manual: no proviene de WooCommerce
            'product_id' => $this->cProductId,
            'customer_name' => $this->cName,
            'customer_email' => $this->cEmail ?: null,
            'total' => round($totals['total'], 2),
            'status' => $this->cMode === 'deposit' ? 'processing' : 'completed',
            'booking_start' => $bookingStart,
            'origin' => $this->cChannel,
            'data' => $data,
        ]);

        Notification::make()->success()->title('Venta creada ✓')
            ->body('Asientos bloqueados · confirmación lista para enviar · comanda enviada a cocina.')->send();

        // reset y volver al listado
        $this->reset(['cName', 'cPhone', 'cEmail', 'cHotel', 'cNote', 'cPacks']);
        $this->cPax = 2;
        $this->syncGuests();
        $this->go('list');
    }

    /** Actividad reciente del detalle. */
    public function activityOf(Order $order): array
    {
        $acts = [];
        if ($order->created_at) {
            $acts[] = ['t' => 'Venta creada', 's' => ($order->origin ?: 'checkout') . ' · ' . $order->created_at->format('d/m H:i')];
        }
        if (in_array($order->status, ['completed', 'processing'], true) && $order->updated_at) {
            $acts[] = ['t' => 'Pago registrado', 's' => 'Estado ' . $order->status . ' · ' . $order->updated_at->format('d/m H:i')];
        }

        return $acts;
    }
}
