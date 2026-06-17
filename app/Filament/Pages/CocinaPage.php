<?php

namespace App\Filament\Pages;

use App\Models\KitchenCount;
use App\Models\KitchenPurchase;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CocinaPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationLabel = 'Cocina';

    protected static ?string $navigationGroup = 'Operaciones';

    protected static string $view = 'filament.pages.cocina-page';

    protected static ?int $navigationSort = 2;

    /** Tab activo: servicio | inventario | reporte */
    public string $activeTab = 'servicio';

    public string $selectedDate;

    /** Hora (turno) seleccionada en el servicio del día */
    public ?string $selectedHour = null;

    /** Estado de cada plato de la comanda: clave "hora-idx" => 0 pend / 1 prep / 2 listo */
    public array $dishStates = [];

    /** Formulario de compra semanal */
    public string $pDate;
    public ?string $pSupplier = 'Mercarne PC';
    public string $pItem = 'Carne';
    public int $pPortions = 40;
    public float $pCost = 380;

    /** Conteo físico por ítem (reporte de conciliación) */
    public array $physicalCounts = [];

    /** Categorías canónicas de plato principal (1 comensal = 1 plato). */
    public const ITEMS = ['Carne', 'Salmón', 'Pollo', 'Vegetariano', 'Vegano'];

    public function getTitle(): string
    {
        return 'Cocina';
    }

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->pDate = now()->format('Y-m-d');
        $this->loadPhysicalCounts();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedSelectedDate(): void
    {
        $this->selectedHour = null;
        $this->dishStates = [];
    }

    public function selectHour(string $hour): void
    {
        $this->selectedHour = $hour;
    }

    /* ======================= helpers de datos ======================= */

    public function dishIcon(string $name): string
    {
        return match ($this->dishCategory($name)) {
            'Carne' => '🥩',
            'Salmón' => '🐟',
            'Pollo' => '🍗',
            'Vegetariano' => '🥗',
            'Vegano' => '🌱',
            default => '🍽️',
        };
    }

    /** Color CSS por categoría de plato. */
    public function dishColor(string $name): string
    {
        return match ($this->dishCategory($name)) {
            'Carne' => '#B2543B',
            'Salmón' => '#E8836B',
            'Pollo' => '#E8B544',
            'Vegetariano' => '#1FA98C',
            'Vegano' => '#3D7BFA',
            default => '#69748A',
        };
    }

    /** Normaliza el nombre real del plato a una de las categorías canónicas. */
    public function dishCategory(string $name): string
    {
        $n = Str::lower($name);

        return match (true) {
            Str::contains($n, ['vegan', 'vegano']) => 'Vegano',
            Str::contains($n, ['veget', 'risotto', 'ensalada', 'salad']) => 'Vegetariano',
            Str::contains($n, ['pollo', 'chicken', 'ave']) => 'Pollo',
            Str::contains($n, ['salmón', 'salmon', 'pescado', 'fish', 'marisc', 'seafood', 'shrimp', 'camar']) => 'Salmón',
            Str::contains($n, ['carne', 'filet', 'mignon', 'beef', 'steak', 'res', 'turf', 'flap', 'prime', 'ribeye']) => 'Carne',
            default => 'Carne',
        };
    }

    /**
     * ¿Es un complemento (transporte, traslado…) y no un plato de comida?
     * No cuenta como comensal ni como plato de cocina.
     */
    public function isAddon(string $name): bool
    {
        $n = Str::lower($name);

        // Transporte/traslado o extra de ocasión (pastel, pack, champagne…): no es plato principal.
        return Str::contains($n, [
            'transport', 'round trip', 'roundtrip', 'transfer', 'traslado',
            'pickup', 'pick up', 'shuttle', 'passenger', 'pasajero', 'recogida',
            'cake', 'pastel', 'birthday', 'cumpleaños', 'anniversary', 'aniversario',
            'pack', 'champagne', 'champán', 'champan', 'bottle', 'botella', 'wine', 'vino',
            'souvenir', 'photo', 'foto', 'upgrade', 'brindis', 'toast',
        ]);
    }

    /** Extrae los platos de comida de una orden desde data.line_items[].meta_data _pao_ids. */
    public function extractProducts($order): array
    {
        $orderData = json_decode(json_encode($order->data), true);
        $lineItems = $orderData['data']['line_items'] ?? null;
        $productos = [];

        if ($lineItems) {
            foreach ($lineItems as $item) {
                foreach ($item['meta_data'] ?? [] as $meta) {
                    if (($meta['key'] ?? null) === '_pao_ids' && isset($meta['value'])) {
                        foreach ($meta['value'] as $pao) {
                            if (isset($pao['key']) && $pao['key'] !== 'Quantity' && ! $this->isAddon($pao['key'])) {
                                $productos[] = [
                                    'name' => $pao['key'],
                                    'quantity' => (int) ($pao['value'] ?? 1),
                                ];
                            }
                        }
                        break;
                    }
                }
                // Fallback: cantidad del line item como platos de la categoría por defecto
                if (empty($productos)) {
                    $productos[] = ['name' => $item['name'] ?? 'Carne', 'quantity' => (int) ($item['quantity'] ?? 1)];
                }
            }
        }

        return $productos;
    }

    public function getOrdersForDate(): Collection
    {
        return Order::query()
            ->whereNotNull('booking_start')
            ->whereNotIn('status', ['pending', 'draft', 'failed', 'cancelled'])
            ->whereDate('booking_start', $this->selectedDate)
            ->with('product')
            ->orderBy('booking_start')
            ->get();
    }

    /** Turnos del día: un turno por hora con su mix de platos y comanda expandida. */
    public function getTurnos(): array
    {
        $turnos = [];

        foreach ($this->getOrdersForDate()->groupBy(fn ($o) => Carbon::parse($o->booking_start)->format('H:i')) as $hour => $orders) {
            $guests = [];
            $mix = [];
            $seat = 1;

            foreach ($orders as $order) {
                foreach ($this->extractProducts($order) as $p) {
                    $cat = $this->dishCategory($p['name']);
                    for ($i = 0; $i < max(1, $p['quantity']); $i++) {
                        $guests[] = [
                            'seat' => $seat++,
                            'name' => $this->cleanName($order->customer_name),
                            'dish' => $cat,
                            'raw' => $p['name'],
                            'order_id' => $order->id,
                        ];
                        $mix[$cat] = ($mix[$cat] ?? 0) + 1;
                    }
                }
            }

            arsort($mix);

            $turnos[] = [
                'hour' => $hour,
                'name' => $orders->first()->product?->name ?? 'Experiencia',
                'pax' => count($guests),
                'guests' => $guests,
                'mix' => $mix,
            ];
        }

        return $turnos;
    }

    protected function cleanName(?string $name): string
    {
        return trim(preg_replace('/\s*\b(viator|ota|hotel)\b\s*/i', ' ', $name ?? '')) ?: 'Cliente';
    }

    public function getCurrentTurno(): ?array
    {
        $turnos = $this->getTurnos();
        if (empty($turnos)) {
            return null;
        }

        $hour = $this->selectedHour;
        foreach ($turnos as $t) {
            if ($t['hour'] === $hour) {
                return $t;
            }
        }

        return $turnos[0];
    }

    public function advanceDish(string $key): void
    {
        $this->dishStates[$key] = (($this->dishStates[$key] ?? 0) + 1) % 3;
    }

    /* ======================= inventario semanal ======================= */

    public function weekRange(): array
    {
        $ref = Carbon::parse($this->selectedDate);

        return [$ref->copy()->startOfWeek(Carbon::MONDAY), $ref->copy()->endOfWeek(Carbon::SUNDAY)];
    }

    /** Platos vendidos (consumidos) en un rango, por categoría. */
    protected function soldByItem(Carbon $from, Carbon $to): array
    {
        $sold = array_fill_keys(self::ITEMS, 0);

        $orders = Order::whereBetween('booking_start', [$from, $to])
            ->whereNotIn('status', ['pending', 'draft', 'failed', 'cancelled'])
            ->get();

        foreach ($orders as $o) {
            foreach ($this->extractProducts($o) as $p) {
                $cat = $this->dishCategory($p['name']);
                $sold[$cat] = ($sold[$cat] ?? 0) + max(1, $p['quantity']);
            }
        }

        return $sold;
    }

    /** Porciones reservadas por vuelos futuros confirmados. */
    protected function reservedByItem(): array
    {
        return $this->soldByItem(Carbon::now(), Carbon::now()->addDays(14));
    }

    public function getInventoryCards(): array
    {
        [$from, $to] = $this->weekRange();
        $purchased = KitchenPurchase::whereBetween('date', [$from, $to])
            ->get()
            ->groupBy(fn ($p) => $this->dishCategory($p->item))
            ->map(fn ($g) => (int) $g->sum('portions'));

        $sold = $this->soldByItem($from, $to);
        $reserved = $this->reservedByItem();

        $cards = [];
        foreach (self::ITEMS as $item) {
            $comp = (int) ($purchased[$item] ?? 0);
            $vend = (int) ($sold[$item] ?? 0);
            $res = (int) ($reserved[$item] ?? 0);
            $disp = $comp - $vend;
            $cards[] = [
                'item' => $item,
                'comp' => $comp,
                'vend' => $vend,
                'res' => $res,
                'disp' => $disp,
                'ok' => $disp >= $res,
                'pct' => $comp > 0 ? min(100, (int) round(max(0, $disp) / $comp * 100)) : 0,
                'color' => $this->dishColor($item),
                'icon' => $this->dishIcon($item),
            ];
        }

        return $cards;
    }

    public function getLedger(): Collection
    {
        [$from, $to] = $this->weekRange();

        return KitchenPurchase::whereBetween('date', [$from, $to])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    public function addPurchase(): void
    {
        $this->validate([
            'pItem' => 'required|string',
            'pPortions' => 'required|integer|min:1',
            'pCost' => 'required|numeric|min:0',
            'pDate' => 'required|date',
        ]);

        KitchenPurchase::create([
            'date' => $this->pDate,
            'supplier' => $this->pSupplier,
            'item' => $this->pItem,
            'portions' => $this->pPortions,
            'cost_total' => $this->pCost,
        ]);

        Notification::make()
            ->success()
            ->title('Compra registrada ✓')
            ->body('El stock disponible se actualizó y la conciliación de la semana ya la incluye.')
            ->send();

        $this->pPortions = 40;
        $this->pCost = 380;
    }

    /* ======================= reporte conciliación ======================= */

    protected function loadPhysicalCounts(): void
    {
        [$from] = $this->weekRange();
        $saved = KitchenCount::where('week_start', $from->toDateString())->pluck('physical_count', 'item')->toArray();

        foreach (self::ITEMS as $item) {
            $this->physicalCounts[$item] = $saved[$item] ?? null;
        }
    }

    public function getReconciliation(): array
    {
        $cards = $this->getInventoryCards();
        $rows = [];
        $totalMerma = 0.0;
        $mermaPortions = 0;

        [$from, $to] = $this->weekRange();
        $costPer = KitchenPurchase::whereBetween('date', [$from, $to])
            ->get()
            ->groupBy(fn ($p) => $this->dishCategory($p->item))
            ->map(function ($g) {
                $port = (int) $g->sum('portions');

                return $port > 0 ? (float) $g->sum('cost_total') / $port : 0.0;
            });

        foreach ($cards as $c) {
            $theoretical = $c['disp'] - $c['res'];
            $physical = $this->physicalCounts[$c['item']];
            $diff = $physical === null ? 0 : $theoretical - (int) $physical;
            $merma = max(0, $diff);
            $cost = (float) ($costPer[$c['item']] ?? 0);
            $totalMerma += $merma * $cost;
            $mermaPortions += $merma;

            $rows[] = [
                'item' => $c['item'],
                'icon' => $c['icon'],
                'comp' => $c['comp'],
                'vend' => $c['vend'],
                'res' => $c['res'],
                'theoretical' => $theoretical,
                'physical' => $physical,
                'merma' => $merma,
                'mermaCost' => $merma * $cost,
                'ok' => $physical !== null && $diff === 0,
            ];
        }

        $totalPurchased = array_sum(array_column($cards, 'comp'));
        $totalSold = array_sum(array_column($cards, 'vend'));

        return [
            'rows' => $rows,
            'totalPurchased' => $totalPurchased,
            'totalSold' => $totalSold,
            'totalCost' => KitchenPurchase::whereBetween('date', [$from, $to])->sum('cost_total'),
            'conciliacion' => $totalPurchased > 0 ? round(($totalSold + array_sum(array_column($cards, 'res'))) / max(1, $totalPurchased) * 100, 1) : 0,
            'mermaValue' => round($totalMerma, 2),
            'mermaPortions' => $mermaPortions,
        ];
    }

    public function closeWeek(): void
    {
        [$from] = $this->weekRange();

        foreach (self::ITEMS as $item) {
            if ($this->physicalCounts[$item] !== null && $this->physicalCounts[$item] !== '') {
                KitchenCount::updateOrCreate(
                    ['week_start' => $from->toDateString(), 'item' => $item],
                    ['physical_count' => (int) $this->physicalCounts[$item]],
                );
            }
        }

        Notification::make()
            ->success()
            ->title('Semana cerrada ✓')
            ->body('La merma quedó registrada y el conteo físico se guardó como base de la próxima semana.')
            ->send();
    }
}
