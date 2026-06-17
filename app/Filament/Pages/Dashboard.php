<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static string $view = 'filament.pages.escritorio';

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationLabel = 'Escritorio';

    public function getTitle(): string
    {
        return 'Escritorio';
    }

    /** Estados considerados "pagados" / "pendientes" segun la data real. */
    protected array $paidStatuses = ['completed', 'processing'];

    protected array $pendingStatuses = ['pending', 'a-la-espera'];

    /**
     * Numero de comensales (pax) de una orden, extraido del meta _pao_ids "Quantity".
     */
    protected function paxOf(Order $order): int
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
     * Canal comercial. Se deriva de origin y, como respaldo, del nombre del
     * cliente (las reservas de OTAs llegan marcadas con "VIATOR" en el nombre).
     */
    protected function channelOf(Order $order): string
    {
        $haystack = strtolower(trim($order->origin . ' ' . $order->customer_name));

        if (str_contains($haystack, 'viator') || str_contains($haystack, 'ota')) {
            return 'viator';
        }

        if (str_contains($haystack, 'hotel')) {
            return 'hoteles';
        }

        return 'web';
    }

    /** Etiqueta de franja horaria segun la hora de la reserva. */
    protected function slotLabel(int $hour): string
    {
        return match (true) {
            $hour < 12 => 'BRUNCH',
            $hour < 16 => 'LUNCH',
            $hour < 19 => 'SUNSET',
            $hour < 21 => 'NIGHT',
            default => 'LEVEL UP',
        };
    }

    /**
     * Clima en vivo para Calle principal Downtown, Punta Cana 23000,
     * Dominican Republic. Se usa Open-Meteo (sin API key) y se cachea
     * 30 minutos para no llamar a la API en cada carga.
     *
     * Límite operativo de viento: 35 km/h.
     */
    protected function weather(): array
    {
        $fallback = [
            'available' => false,
            'wind' => null,
            'temp' => null,
            'condition' => 'Sin datos',
            'rain' => null,
            'operational' => true,
            'wind_limit' => 35,
        ];

        return Cache::remember('escritorio.weather', now()->addMinutes(30), function () use ($fallback) {
            try {
                $res = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => 18.5818,   // Downtown Punta Cana
                    'longitude' => -68.4055,
                    'current' => 'temperature_2m,weather_code,wind_speed_10m,precipitation',
                    'wind_speed_unit' => 'kmh',
                    'timezone' => 'America/Santo_Domingo',
                ]);

                if (! $res->ok()) {
                    return $fallback;
                }

                $cur = $res->json('current') ?? [];

                if (! isset($cur['wind_speed_10m'], $cur['temperature_2m'])) {
                    return $fallback;
                }

                $wind = (int) round($cur['wind_speed_10m']);
                $code = (int) ($cur['weather_code'] ?? 0);

                return [
                    'available' => true,
                    'wind' => $wind,
                    'temp' => (int) round($cur['temperature_2m']),
                    'condition' => $this->weatherLabel($code),
                    'rain' => $this->weatherIsRain($code) || (float) ($cur['precipitation'] ?? 0) > 0,
                    'operational' => $wind <= 35,
                    'wind_limit' => 35,
                ];
            } catch (\Throwable $e) {
                return $fallback;
            }
        });
    }

    /** Etiqueta legible para un código WMO de Open-Meteo. */
    protected function weatherLabel(int $code): string
    {
        return match (true) {
            $code === 0 => 'Despejado',
            in_array($code, [1, 2], true) => 'Parcialmente nublado',
            $code === 3 => 'Nublado',
            in_array($code, [45, 48], true) => 'Niebla',
            in_array($code, [51, 53, 55, 56, 57], true) => 'Llovizna',
            in_array($code, [61, 63, 65, 66, 67], true) => 'Lluvia',
            in_array($code, [71, 73, 75, 77], true) => 'Nieve',
            in_array($code, [80, 81, 82], true) => 'Chubascos',
            in_array($code, [95, 96, 99], true) => 'Tormenta',
            default => 'Variable',
        };
    }

    /** Indica si el código WMO corresponde a precipitación. */
    protected function weatherIsRain(int $code): bool
    {
        return $code >= 51 && $code <= 99;
    }

    public function getViewData(): array
    {
        $today = Carbon::today();
        $now = Carbon::now();

        // ===== TABLERO DE RESERVAS DE HOY =====
        $todayOrders = Order::whereDate('booking_start', $today)
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->with('product')
            ->orderBy('booking_start')
            ->get();

        $flights = $todayOrders
            ->groupBy(fn (Order $o) => $o->booking_start->format('H:i') . '#' . $o->product_id)
            ->map(function (Collection $group) {
                $first = $group->first();
                $cap = (int) ($first->product?->default_capacity ?: 22);
                $sold = $group->sum(fn (Order $o) => $this->paxOf($o));
                $unpaid = $group->whereIn('status', $this->pendingStatuses)
                    ->sum(fn (Order $o) => $this->paxOf($o));

                return [
                    'time' => $first->booking_start->format('H:i'),
                    'slot' => $this->slotLabel($first->booking_start->hour),
                    'name' => $first->product?->name ?? 'Experiencia',
                    'cap' => $cap,
                    'sold' => min($sold, $cap),
                    'unpaid' => (int) $unpaid,
                    'pct' => $cap > 0 ? (int) round(min($sold, $cap) / $cap * 100) : 0,
                ];
            })
            ->values();

        $dayCap = $flights->sum('cap');
        $daySold = $flights->sum('sold');
        $dayRevenue = (float) $todayOrders->sum('total');

        // ===== ALERTAS ACCIONABLES =====
        $paidNoDate = Order::whereIn('status', $this->paidStatuses)
            ->whereNull('booking_start')
            ->get();

        $pendingSoon = Order::whereIn('status', $this->pendingStatuses)
            ->whereNotNull('booking_start')
            ->whereBetween('booking_start', [$now, $now->copy()->addHours(72)])
            ->get();

        $reviewsPending = Order::where('status', 'completed')
            ->whereDate('booking_start', $today->copy()->subDay())
            ->count();

        // ===== KPIs COMERCIALES =====
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $prevStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $prevEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();

        // Ingresos por fecha de pago (date_paid) para coincidir con WooCommerce Analytics;
        // si la orden no tiene fecha de pago, cae a created_at. Ambas se conservan desde WooCommerce.
        $revMonth = (float) Order::whereIn('status', $this->paidStatuses)
            ->whereRaw('COALESCE(date_paid, created_at) BETWEEN ? AND ?', [$monthStart->toDateTimeString(), $monthEnd->toDateTimeString()])
            ->sum('total');
        $revPrev = (float) Order::whereIn('status', $this->paidStatuses)
            ->whereRaw('COALESCE(date_paid, created_at) BETWEEN ? AND ?', [$prevStart->toDateTimeString(), $prevEnd->toDateTimeString()])
            ->sum('total');
        $revDelta = $revPrev > 0 ? (int) round(($revMonth - $revPrev) / $revPrev * 100) : null;

        // Ocupacion ultimos 7 dias
        $weekOrders = Order::whereBetween('booking_start', [$now->copy()->subDays(7), $now])
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->with('product')
            ->get();
        $weekCap = $weekOrders
            ->groupBy(fn (Order $o) => $o->booking_start->format('Y-m-d H:i') . '#' . $o->product_id)
            ->sum(fn (Collection $g) => (int) ($g->first()->product?->default_capacity ?: 22));
        $weekSold = $weekOrders->sum(fn (Order $o) => $this->paxOf($o));
        $occ7 = $weekCap > 0 ? (int) round($weekSold / $weekCap * 100) : 0;

        // Mix de canales (mes actual)
        $monthOrders = Order::whereBetween('booking_start', [$monthStart, $monthEnd])->get();
        $chan = ['web' => 0, 'viator' => 0, 'hoteles' => 0];
        foreach ($monthOrders as $o) {
            $chan[$this->channelOf($o)]++;
        }
        $chanTotal = max(array_sum($chan), 1);
        $mix = [
            'web' => (int) round($chan['web'] / $chanTotal * 100),
            'viator' => (int) round($chan['viator'] / $chanTotal * 100),
            'hoteles' => (int) round($chan['hoteles'] / $chanTotal * 100),
        ];

        // Attach rate (packs premium ~ additional_dishes)
        $attachWith = $monthOrders->filter(fn (Order $o) => ! empty($o->additional_dishes))->count();
        $attach = $monthOrders->count() > 0
            ? (int) round($attachWith / $monthOrders->count() * 100)
            : 0;

        // ===== ORDENES QUE REQUIEREN ATENCION (proximos 7 dias) =====
        $attention = Order::query()
            ->where(function ($q) use ($today, $now) {
                $q->whereBetween('booking_start', [$today, $now->copy()->addDays(7)->endOfDay()])
                    ->orWhereNull('booking_start');
            })
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->with('product')
            ->orderByRaw('booking_start IS NULL DESC')
            ->orderBy('booking_start')
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'date' => $o->booking_start,
                'slot' => $o->booking_start ? $this->slotLabel($o->booking_start->hour) : null,
                'name' => trim(preg_replace('/\s*\b(viator|ota)\b\s*/i', ' ', $o->customer_name ?? '')) ?: 'Sin nombre',
                'contact' => $o->customer_email ?: '—',
                'pax' => $this->paxOf($o),
                'product' => $o->product?->name ?? '—',
                'channel' => $this->channelOf($o),
                'paid' => in_array($o->status, $this->paidStatuses, true),
                'total' => (float) $o->total,
                'url' => \App\Filament\Resources\OrderResource::getUrl('view', ['record' => $o->id]),
            ]);

        return [
            'todayLabel' => $today->locale('es')->isoFormat('dddd, D [de] MMMM YYYY'),
            'flights' => $flights,
            'dayCap' => $dayCap,
            'daySold' => $daySold,
            'dayPct' => $dayCap > 0 ? (int) round($daySold / $dayCap * 100) : 0,
            'dayRevenue' => $dayRevenue,
            'alertNoDate' => [
                'count' => $paidNoDate->count(),
                'sum' => (float) $paidNoDate->sum('total'),
                'url' => \App\Filament\Pages\VentasPage::getUrl(['filter' => 'nodate-paid']),
            ],
            'alertPendingSoon' => [
                'count' => $pendingSoon->count(),
                'sum' => (float) $pendingSoon->sum('total'),
                'url' => \App\Filament\Pages\VentasPage::getUrl(['filter' => 'unpaid-soon']),
            ],
            'alertReviews' => $reviewsPending,
            'revMonth' => $revMonth,
            'revDelta' => $revDelta,
            'monthName' => $now->locale('es')->isoFormat('MMMM'),
            'occ7' => $occ7,
            'mix' => $mix,
            'attach' => $attach,
            'attention' => $attention,
            'weather' => $this->weather(),
        ];
    }
}
