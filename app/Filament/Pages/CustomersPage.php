<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;

class CustomersPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Clientes';
    
    protected static ?string $navigationGroup = 'Reportes';
    
    protected static string $view = 'filament.pages.customers-page';

    protected static ?int $navigationSort = 2;

    public function getTitle(): string
    {
        return 'Reporte de Clientes';
    }

    /**
     * Get customers data aggregated from orders
     */
    public function getCustomersData(): Collection
    {
        try {
            return Order::selectRaw('
                    customer_email,
                    customer_name,
                    billing_phone,
                    billing_city,
                    billing_state,
                    billing_country,
                    COUNT(*) as total_orders,
                    SUM(total) as total_amount,
                    MIN(date_created) as first_order_date,
                    MAX(date_created) as last_order_date,
                    GROUP_CONCAT(DISTINCT status) as order_statuses
                ')
                ->whereNotNull('customer_email')
                ->where('customer_email', '!=', '')
                ->groupBy('customer_email', 'customer_name', 'billing_phone', 'billing_city', 'billing_state', 'billing_country')
                ->orderByDesc('total_amount')
                ->get();
        } catch (QueryException $e) {
            // Fallback for older database structure
            return Order::selectRaw('
                    customer_email,
                    customer_name,
                    COUNT(*) as total_orders,
                    SUM(total) as total_amount,
                    MIN(created_at) as first_order_date,
                    MAX(created_at) as last_order_date,
                    GROUP_CONCAT(DISTINCT status) as order_statuses
                ')
                ->whereNotNull('customer_email')
                ->where('customer_email', '!=', '')
                ->groupBy('customer_email', 'customer_name')
                ->orderByDesc('total_amount')
                ->get();
        }
    }

    /**
     * Get customer statistics
     */
    public function getCustomerStats(): array
    {
        $customers = $this->getCustomersData();
        
        return [
            'total_customers' => $customers->count(),
            'total_revenue' => $customers->sum('total_amount'),
            'avg_orders_per_customer' => $customers->avg('total_orders'),
            'avg_revenue_per_customer' => $customers->avg('total_amount'),
            'top_customer' => $customers->first(),
        ];
    }

    /**
     * Get formatted currency
     */
    public function formatCurrency($amount): string
    {
        return '$' . number_format($amount, 2);
    }

    /**
     * Get customer status badge color
     */
    public function getStatusColor($statuses): string
    {
        $statusArray = explode(',', $statuses);
        
        if (in_array('pending', $statusArray)) {
            return 'warning';
        } elseif (in_array('completed', $statusArray)) {
            return 'success';
        } elseif (in_array('cancelled', $statusArray)) {
            return 'danger';
        }
        
        return 'gray';
    }

    /**
     * Get customer status text
     */
    public function getStatusText($statuses): string
    {
        $statusArray = explode(',', $statuses);
        $statusCount = array_count_values($statusArray);
        
        if (isset($statusCount['completed']) && count($statusArray) === $statusCount['completed']) {
            return 'Todos Completados';
        } elseif (isset($statusCount['pending'])) {
            return 'Tiene Pendientes';
        } elseif (isset($statusCount['cancelled'])) {
            return 'Tiene Cancelados';
        }
        
        return 'Mixto';
    }
}
