<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use WpOrg\Requests\Requests;

class DiagnoseWooDiff extends Command
{
    protected $signature = 'orders:diagnose-woo {--status=any : Estado a comparar (any = todos)}';

    protected $description = 'Compara las órdenes de WooCommerce contra la base local y muestra diferencias de conteo y monto (solo lectura)';

    public function handle(): int
    {
        $base = rtrim(config('woocommerce.base_url'), '/');
        $auth = config('woocommerce.auth');
        $statusOpt = $this->option('status');

        $this->info('Trayendo órdenes de WooCommerce...');

        $woo = [];          // id => ['status' => , 'total' => , 'date' => ]
        $page = 1;
        do {
            $url = $base . '/wp-json/wc/v3/orders?per_page=100&page=' . $page
                . ($statusOpt !== 'any' ? '&status=' . $statusOpt : '');
            $r = Requests::get($url, ['Authorization' => $auth]);

            if (! $r->success) {
                $this->error("Error en página {$page}: HTTP {$r->status_code} - " . substr($r->body, 0, 200));
                return 1;
            }

            $orders = json_decode($r->body, true);
            if (empty($orders)) {
                break;
            }

            foreach ($orders as $o) {
                $woo[$o['id']] = [
                    'status' => $o['status'],
                    'total' => (float) $o['total'],
                    'date' => $o['date_created'] ?? null,
                ];
            }

            $this->line("  Página {$page}: " . count($orders) . ' órdenes (acumulado ' . count($woo) . ')');
            $page++;
        } while (count($orders) === 100);

        // ----- Totales WooCommerce -----
        $wooByStatus = [];
        $wooGrand = 0.0;
        foreach ($woo as $o) {
            $wooByStatus[$o['status']] ??= ['c' => 0, 't' => 0.0];
            $wooByStatus[$o['status']]['c']++;
            $wooByStatus[$o['status']]['t'] += $o['total'];
            $wooGrand += $o['total'];
        }

        // ----- Totales DB -----
        $db = Order::query()
            ->whereNotNull('woocommerce_order_id')
            ->get(['woocommerce_order_id', 'status', 'total'])
            ->keyBy('woocommerce_order_id');

        $this->newLine();
        $this->info('=== WOOCOMMERCE (por estado) ===');
        foreach ($wooByStatus as $st => $v) {
            $this->line('  ' . str_pad($st, 14) . ' count=' . str_pad($v['c'], 5) . ' total=' . number_format($v['t'], 2));
        }
        $this->line('  TOTAL Woo: ' . count($woo) . ' órdenes, $' . number_format($wooGrand, 2));

        $this->newLine();
        $this->info('=== SISTEMA (DB) ===');
        $this->line('  ' . $db->count() . ' órdenes con woo_id, total=$' . number_format((float) $db->sum('total'), 2));

        // ----- Faltantes: en Woo pero no en DB -----
        $missing = [];
        $missingSum = 0.0;
        foreach ($woo as $id => $o) {
            if (! $db->has($id)) {
                $missing[] = ['id' => $id] + $o;
                $missingSum += $o['total'];
            }
        }

        $this->newLine();
        $this->info('=== FALTANTES (en Woo, no en el sistema) ===');
        if (empty($missing)) {
            $this->line('  Ninguna. Todas las órdenes de Woo están en el sistema.');
        } else {
            foreach ($missing as $m) {
                $this->line("  #{$m['id']}  {$m['status']}  \$" . number_format($m['total'], 2) . "  {$m['date']}");
            }
            $this->error('  ' . count($missing) . ' órdenes faltantes, suman $' . number_format($missingSum, 2));
        }

        // ----- Diferencias de monto en órdenes que sí están -----
        $diffs = [];
        $diffSum = 0.0;
        foreach ($woo as $id => $o) {
            if ($db->has($id)) {
                $dbTotal = (float) $db[$id]->total;
                if (abs($dbTotal - $o['total']) > 0.01) {
                    $diffs[] = ['id' => $id, 'woo' => $o['total'], 'db' => $dbTotal];
                    $diffSum += ($o['total'] - $dbTotal);
                }
            }
        }

        $this->newLine();
        $this->info('=== DIFERENCIAS DE MONTO (misma orden, total distinto) ===');
        if (empty($diffs)) {
            $this->line('  Ninguna.');
        } else {
            foreach ($diffs as $d) {
                $this->line("  #{$d['id']}  woo=\$" . number_format($d['woo'], 2) . '  db=$' . number_format($d['db'], 2));
            }
            $this->line('  Diferencia neta por montos: $' . number_format($diffSum, 2));
        }

        $this->newLine();
        $this->info('=== RESUMEN ===');
        $gap = $wooGrand - (float) $db->sum('total');
        $this->line('  Woo total - Sistema total = $' . number_format($gap, 2));
        $this->line('  Explicado por faltantes: $' . number_format($missingSum, 2));
        $this->line('  Explicado por diferencias de monto: $' . number_format($diffSum, 2));

        return 0;
    }
}
