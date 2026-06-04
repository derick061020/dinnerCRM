<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;

class CocinaPage extends Page
{
    use \Livewire\WithFileUploads;
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    
    protected static ?string $navigationLabel = 'Cocina';
    
    protected static ?string $navigationGroup = 'Operaciones';
    
    protected static string $view = 'filament.pages.cocina-page';

    protected static ?int $navigationSort = 1;

    public $selectedDate;
    public $selectedHour;

    public function getTitle(): string
    {
        return 'Vista de Cocina';
    }

    public function mount(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
        $this->selectedHour = null;
    }

    /**
     * Handle date changes
     */
    public function updatedSelectedDate(): void
    {
        $this->selectedHour = null; // Reset selected hour when date changes
    }

    /**
     * Obtener órdenes para la fecha seleccionada
     */
    public function getOrdersForDate(): Collection
    {
        return Order::query()
            ->whereNotNull('booking_start')
            ->whereNotIn('status', ['pending', 'draft', 'failed', 'cancelled'])
            ->whereDate('booking_start', $this->selectedDate)
            ->orderBy('booking_start', 'asc')
            ->get();
    }

    /**
     * Obtener órdenes agrupadas por hora
     */
    public function getOrdersGroupedByHour(): Collection
    {
        return $this->getOrdersForDate()
            ->groupBy(function($order) {
                return date('H:00', strtotime($order->booking_start));
            });
    }

    /**
     * Obtener órdenes para una hora específica
     */
    public function getOrdersForHour(string $hour): Collection
    {
        return $this->getOrdersForDate()
            ->filter(function($order) use ($hour) {
                return date('H:00', strtotime($order->booking_start)) === $hour;
            });
    }

    /**
     * Obtener total de productos para la fecha
     */
    public function getTotalProductsForDate(): int
    {
        return array_sum($this->getProductSummary($this->getOrdersForDate()));
    }

    /**
     * Extraer los productos (platos) de una orden a partir del campo data.
     *
     * @return array<int, array{name: string, quantity: int}>
     */
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
                            if (isset($pao['key']) && $pao['key'] !== 'Quantity') {
                                $productos[] = [
                                    'name'     => $pao['key'],
                                    'quantity' => (int) ($pao['value'] ?? 1),
                                ];
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $productos;
    }

    /**
     * Resumen agregado de platos para un conjunto de órdenes.
     * Devuelve [nombre_plato => cantidad_total] ordenado de mayor a menor.
     *
     * @return array<string, int>
     */
    public function getProductSummary(Collection $orders): array
    {
        $resumen = [];

        foreach ($orders as $order) {
            foreach ($this->extractProducts($order) as $producto) {
                $name = $producto['name'];
                $resumen[$name] = ($resumen[$name] ?? 0) + $producto['quantity'];
            }
        }

        arsort($resumen);

        return $resumen;
    }

    /**
     * Total de platos a preparar para una hora concreta.
     */
    public function getDishCountForHour(string $hour): int
    {
        return array_sum($this->getProductSummary($this->getOrdersForHour($hour)));
    }

    /**
     * Devuelve un emoji representativo del plato para una lectura visual rápida.
     */
    public function dishIcon(string $name): string
    {
        $n = Str::lower($name);

        return match (true) {
            Str::contains($n, ['filet', 'mignon', 'carne', 'beef', 'steak']) => '🥩',
            Str::contains($n, ['salmón', 'salmon', 'pescado', 'fish', 'marisc', 'marinera']) => '🐟',
            Str::contains($n, ['risotto', 'paella', 'arroz', 'pasta', 'hongos']) => '🍝',
            Str::contains($n, ['ensalada', 'salad', 'césar', 'cesar']) => '🥗',
            Str::contains($n, ['sopa', 'soup', 'crema']) => '🍲',
            Str::contains($n, ['postre', 'tiramisú', 'tiramisu', 'dessert', 'dulce']) => '🍰',
            Str::contains($n, ['champagne', 'champán', 'copa', 'vino', 'wine', 'bebida']) => '🥂',
            Str::contains($n, ['queso', 'cheese', 'tabla']) => '🧀',
            Str::contains($n, ['menú', 'menu', 'degustación', 'premium']) => '⭐',
            default => '🍽️',
        };
    }
}
