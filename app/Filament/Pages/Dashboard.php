<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

        $revMonth = (float) Order::whereIn('status', $this->paidStatuses)
            ->whereBetween('booking_start', [$monthStart, $monthEnd])
            ->sum('total');
        $revPrev = (float) Order::whereIn('status', $this->paidStatuses)
            ->whereBetween('booking_start', [$prevStart, $prevEnd])
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
            'alertNoDate' => ['count' => $paidNoDate->count(), 'sum' => (float) $paidNoDate->sum('total')],
            'alertPendingSoon' => ['count' => $pendingSoon->count(), 'sum' => (float) $pendingSoon->sum('total')],
            'alertReviews' => $reviewsPending,
            'revMonth' => $revMonth,
            'revDelta' => $revDelta,
            'monthName' => $now->locale('es')->isoFormat('MMMM'),
            'occ7' => $occ7,
            'mix' => $mix,
            'attach' => $attach,
            'attention' => $attention,
        ];
    }
}
