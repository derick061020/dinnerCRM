<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Inventory;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisMonthEnd = Carbon::now()->endOfMonth();

        // Estadísticas de órdenes
        $totalOrders = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $completedOrders = Order::where('status', 'completed')->count();
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $todayBookings = Order::whereDate('booking_start', $today)->count();

        // Estadísticas de ingresos
        $totalRevenue = Order::where('status', 'completed')->sum('total');
        $monthlyRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$thisMonth, $thisMonthEnd])
            ->sum('total');
        $todayRevenue = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total');

        // Estadísticas de productos
        $totalProducts = Product::count();
        $activeProducts = Product::whereHas('timeslots', function ($query) {
            $query->where('active', true);
        })->count();

        // Estadísticas de capacidad
        $todayInventory = Inventory::where('date', $today)->get();
        $totalCapacity = $todayInventory->sum('capacity_total');
        $usedCapacity = $todayInventory->sum('capacity_used');
        $availableCapacity = $totalCapacity - $usedCapacity;
        $occupancyRate = $totalCapacity > 0 ? ($usedCapacity / $totalCapacity) * 100 : 0;

        return [
            // Estadísticas principales (solo 6)
            Stat::make('Órdenes Totales', number_format($totalOrders))
                ->description($totalOrders > 0 ? '+' . $todayOrders . ' hoy' : 'Sin órdenes')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary')
                ->chart([7, 12, 10, 14, 15, 18, $todayOrders]),

            Stat::make('Ingresos Totales', '$' . number_format($totalRevenue, 2))
                ->description($monthlyRevenue > 0 ? '$' . number_format($monthlyRevenue, 2) . ' este mes' : 'Sin ingresos')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([1000, 1500, 1200, 1800, 2000, 2200, $todayRevenue]),

            Stat::make('Reservas Hoy', $todayBookings)
                ->description($totalCapacity > 0 ? $availableCapacity . ' disponibles' : 'Sin capacidad definida')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->chart([5, 8, 6, 10, 12, 9, $todayBookings]),

            Stat::make('Tasa de Ocupación', number_format($occupancyRate, 1) . '%')
                ->description($availableCapacity . ' lugares libres')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($occupancyRate > 80 ? 'danger' : ($occupancyRate > 50 ? 'warning' : 'success'))
                ->chart([60, 65, 70, 75, 80, 85, $occupancyRate]),

            Stat::make('Órdenes Pendientes', $pendingOrders)
                ->description($pendingOrders > 0 ? 'requieren atención' : 'Todo al día')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('Productos Activos', $activeProducts)
                ->description('de ' . $totalProducts . ' totales')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}
