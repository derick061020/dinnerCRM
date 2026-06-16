<?php

namespace App\Filament\Pages;

use App\Models\KitchenPurchase;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomersPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $navigationGroup = 'Reportes';

    protected static string $view = 'filament.pages.customers-page';

    protected static ?int $navigationSort = 1;

    /** Reporte activo: clientes | canales | gerencia */
    public string $report = 'clientes';

    /** Segmento activo del reporte de clientes */
    public string $segment = 'all';

    public string $search = '';

    public string $from;
    public string $to;

    /** Cliente seleccionado para el modal */
    public ?string $selectedEmail = null;

    /** Desbloqueo de gerencia (rol admin) */
    public bool $gerenciaUnlocked = false;

    protected ?Collection $cache = null;

    public function getTitle(): string
    {
        return 'Centro de Reportes';
    }

    public function getHeading(): string
    {
        return '';
    }

    public function mount(): void
    {
        $this->to = now()->format('Y-m-d');
        $this->from = now()->subMonth()->format('Y-m-d');
    }

    public function setReport(string $r): void
    {
        $this->report = $r;
        $this->selectedEmail = null;
    }

    public function setSegment(string $s): void
    {
        $this->segment = $s;
    }

    public function isAdmin(): bool
    {
        return (auth()->user()->role ?? null) === 'admin';
    }

    public function unlockGerencia(): void
    {
        $this->gerenciaUnlocked = true;
    }

    /* ======================= agregación de clientes ======================= */

    public function getCustomersData(): Collection
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        try {
            $this->cache = Order::selectRaw('
                    customer_email, customer_name,
                    COUNT(*) as total_orders,
                    SUM(total) as total_amount,
                    MIN(created_at) as first_order_date,
                    MAX(booking_start) as last_visit,
                    GROUP_CONCAT(DISTINCT status) as order_statuses
                ')
                ->whereNotNull('customer_email')
                ->where('customer_email', '!=', '')
                ->groupBy('customer_email', 'customer_name')
                ->get();
        } catch (QueryException $e) {
            $this->cache = collect();
        }

        return $this->cache;
    }

    protected function cleanName(?string $name): string
    {
        return trim(preg_replace('/\s*\b(viator|ota|hotel)\b\s*/i', ' ', $name ?? '')) ?: 'Cliente';
    }

    public function channelOf(?string $name): string
    {
        $n = Str::lower($name ?? '');
        if (Str::contains($n, ['viator', 'ota'])) {
            return 'via';
        }
        if (Str::contains($n, 'hotel')) {
            return 'hot';
        }
        if (Str::contains($n, ['instagram', ' ig'])) {
            return 'ig';
        }

        return 'web';
    }

    public function segmentOf($c): string
    {
        $visits = (int) $c->total_orders;
        $spend = (float) $c->total_amount;
        $first = $c->first_order_date ? Carbon::parse($c->first_order_date) : null;
        $last = $c->last_visit ? Carbon::parse($c->last_visit) : null;

        if ($visits >= 2 && $spend >= 1000) {
            return 'vip';
        }
        if ($visits >= 2) {
            return 'rep';
        }
        if ($last && $last->lt(now()->subMonths(6))) {
            return 'ina';
        }
        if ($first && $first->gte(now()->subDays(30))) {
            return 'new';
        }

        return 'reg';
    }

    /** Clientes filtrados por segmento + búsqueda (no paginado, dataset chico). */
    public function getCustomers(): Collection
    {
        $list = $this->getCustomersData();

        $search = trim(Str::lower($this->search));
        if ($search !== '') {
            $list = $list->filter(fn ($c) => Str::contains(Str::lower($c->customer_name ?? ''), $search)
                || Str::contains(Str::lower($c->customer_email ?? ''), $search));
        }

        if ($this->segment !== 'all') {
            $list = $this->segment === 'rep'
                ? $list->filter(fn ($c) => (int) $c->total_orders >= 2)
                : $list->filter(fn ($c) => $this->segmentOf($c) === $this->segment);
        }

        return $list->sortByDesc(fn ($c) => (float) $c->total_amount)->values();
    }

    public function getClientStats(): array
    {
        $all = $this->getCustomersData();
        $total = $all->count();
        $rep = $all->filter(fn ($c) => (int) $c->total_orders >= 2)->count();
        $new = $all->filter(fn ($c) => $this->segmentOf($c) === 'new')->count();

        return [
            'total' => $total,
            'rep' => $total > 0 ? round($rep / $total * 100, 1) : 0,
            'repCount' => $rep,
            'ticket' => $total > 0 ? round($all->sum('total_amount') / max(1, $all->sum('total_orders'))) : 0,
            'new' => $new,
            'vip' => $all->filter(fn ($c) => $this->segmentOf($c) === 'vip')->count(),
            'ina' => $all->filter(fn ($c) => $this->segmentOf($c) === 'ina')->count(),
        ];
    }

    public function segmentCounts(): array
    {
        $all = $this->getCustomersData();

        return [
            'all' => $all->count(),
            'vip' => $all->filter(fn ($c) => $this->segmentOf($c) === 'vip')->count(),
            'rep' => $all->filter(fn ($c) => (int) $c->total_orders >= 2)->count(),
            'new' => $all->filter(fn ($c) => $this->segmentOf($c) === 'new')->count(),
            'ina' => $all->filter(fn ($c) => $this->segmentOf($c) === 'ina')->count(),
        ];
    }

    /* ======================= modal cliente ======================= */

    public function viewCustomer(string $email): void
    {
        $this->selectedEmail = $email;
    }

    public function closeCustomer(): void
    {
        $this->selectedEmail = null;
    }

    public function getSelectedCustomer()
    {
        return $this->selectedEmail
            ? $this->getCustomersData()->firstWhere('customer_email', $this->selectedEmail)
            : null;
    }

    public function getSelectedOrders(): Collection
    {
        if (! $this->selectedEmail) {
            return collect();
        }

        return Order::where('customer_email', $this->selectedEmail)
            ->with('product')
            ->orderByDesc('booking_start')
            ->get();
    }

    public function getSelectedPreferences(): array
    {
        $prefs = [];
        foreach ($this->getSelectedOrders() as $o) {
            $data = json_decode(json_encode($o->data), true);
            foreach ($data['data']['line_items'] ?? [] as $item) {
                foreach ($item['meta_data'] ?? [] as $meta) {
                    if (($meta['key'] ?? null) === '_pao_ids') {
                        foreach ($meta['value'] ?? [] as $pao) {
                            if (($pao['key'] ?? null) !== 'Quantity') {
                                $prefs['🍽 ' . $pao['key']] = true;
                            }
                        }
                    }
                }
            }
            foreach ($data['data']['packs'] ?? [] as $pack) {
                $prefs['🎁 ' . $pack] = true;
            }
        }

        return array_keys($prefs);
    }

    /* ======================= reporte por canal ======================= */

    public function getChannelReport(): array
    {
        $orders = Order::whereBetween('booking_start', [$this->from, $this->to . ' 23:59:59'])
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->get();

        $chan = ['web' => 0, 'via' => 0, 'ig' => 0, 'hot' => 0];
        $rev = ['web' => 0.0, 'via' => 0.0, 'ig' => 0.0, 'hot' => 0.0];
        foreach ($orders as $o) {
            $c = $this->channelOf($o->customer_name);
            $chan[$c]++;
            $rev[$c] += (float) $o->total;
        }
        $totalSales = max(1, array_sum($chan));

        return [
            'sales' => array_sum($chan),
            'orders' => $orders,
            'chan' => $chan,
            'rev' => $rev,
            'mix' => [
                'web' => round($chan['web'] / $totalSales * 100),
                'via' => round($chan['via'] / $totalSales * 100),
                'ig' => round($chan['ig'] / $totalSales * 100),
                'hot' => round($chan['hot'] / $totalSales * 100),
            ],
            'ota' => round(($chan['via']) / $totalSales * 100),
        ];
    }

    /* ======================= reporte gerencia ======================= */

    public function getGerencia(): array
    {
        $orders = Order::whereBetween('booking_start', [$this->from, $this->to . ' 23:59:59'])
            ->whereNotIn('status', ['cancelled', 'failed'])
            ->get();

        $total = (float) $orders->sum('total');
        $viatorRev = (float) $orders->filter(fn ($o) => $this->channelOf($o->customer_name) === 'via')->sum('total');
        $discounts = (float) $orders->sum('discount_total');
        $merma = (float) KitchenPurchase::whereBetween('date', [$this->from, $this->to])->sum('cost_total') * 0; // placeholder cocina
        $ch = $this->getChannelReport();

        return [
            'total' => $total,
            'commission' => round($viatorRev * 0.25, 2),
            'commissionPct' => $total > 0 ? round($viatorRev * 0.25 / $total * 100, 1) : 0,
            'discounts' => $discounts,
            'merma' => $merma,
            'revByChannel' => $ch['rev'],
        ];
    }

    /* ======================= export CSV (real) ======================= */

    public function exportCsv(): StreamedResponse
    {
        $rows = $this->getCustomers();
        $filename = 'clientes_' . $this->segment . '_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Cliente', 'Email', 'Visitas', 'Gasto total', 'Última visita', 'Segmento']);
            foreach ($rows as $c) {
                fputcsv($out, [
                    $this->cleanName($c->customer_name),
                    $c->customer_email,
                    $c->total_orders,
                    number_format((float) $c->total_amount, 2),
                    $c->last_visit ? Carbon::parse($c->last_visit)->format('d/m/Y') : '',
                    Str::upper($this->segmentOf($c)),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
