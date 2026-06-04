<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->getCustomerStats()['total_customers'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Ingresos Totales</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->formatCurrency($this->getCustomerStats()['total_revenue']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Prom. Órdenes</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($this->getCustomerStats()['avg_orders_per_customer'], 1) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Prom. por Cliente</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $this->formatCurrency($this->getCustomerStats()['avg_revenue_per_customer']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Mejor Cliente</p>
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            @if($this->getCustomerStats()['top_customer'])
                                {{ Str::limit($this->getCustomerStats()['top_customer']->customer_name, 15) }}
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input 
                        type="text" 
                        id="customer-search"
                        placeholder="Buscar por nombre o email..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        onkeyup="filterCustomers()"
                    />
                </div>
                <div class="flex gap-2">
                    <select id="status-filter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="filterCustomers()">
                        <option value="">Todos los estados</option>
                        <option value="completed">Todos Completados</option>
                        <option value="pending">Tiene Pendientes</option>
                        <option value="cancelled">Tiene Cancelados</option>
                    </select>
                    <select id="sort-by" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="sortCustomers()">
                        <option value="total_amount">Ordenar por Ingresos</option>
                        <option value="total_orders">Ordenar por Órdenes</option>
                        <option value="customer_name">Ordenar por Nombre</option>
                        <option value="last_order_date">Ordenar por Última Orden</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="customers-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Cliente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contacto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ubicación
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Órdenes
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Gastado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fechas
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $customers = $this->getCustomersData();
                        @endphp
                        @foreach($customers as $customer)
                            <tr class="hover:bg-gray-50 customer-row" 
                                data-name="{{ strtolower($customer->customer_name ?? '') }}" 
                                data-email="{{ strtolower($customer->customer_email ?? '') }}"
                                data-status="{{ $this->getStatusText($customer->order_statuses) }}"
                                data-total-amount="{{ $customer->total_amount }}"
                                data-total-orders="{{ $customer->total_orders }}"
                                data-customer-name="{{ $customer->customer_name ?? '' }}"
                                data-last-order="{{ $customer->last_order_date ? (is_string($customer->last_order_date) ? strtotime($customer->last_order_date) : $customer->last_order_date->timestamp) : 0 }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ strtoupper(substr($customer->customer_name ?? 'U', 0, 1)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div style="margin-left:10px" class="ml-6">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $customer->customer_name ?? 'Sin Nombre' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $customer->customer_email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($customer->billing_phone)
                                            {{ $customer->billing_phone }}
                                        @else
                                            <span class="text-gray-400">No registrado</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        @if($customer->billing_city)
                                            {{ $customer->billing_city }}
                                            @if($customer->billing_state)
                                                , {{ $customer->billing_state }}
                                            @endif
                                        @else
                                            <span class="text-gray-400">No registrada</span>
                                        @endif
                                    </div>
                                    @if($customer->billing_country)
                                        <div class="text-xs text-gray-500">{{ $customer->billing_country }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $customer->total_orders }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $this->formatCurrency($customer->total_amount) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Prom: {{ $this->formatCurrency($customer->total_amount / $customer->total_orders) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($this->getStatusColor($customer->order_statuses) == 'success') bg-green-100 text-green-800
                                        @elseif($this->getStatusColor($customer->order_statuses) == 'warning') bg-yellow-100 text-yellow-800
                                        @elseif($this->getStatusColor($customer->order_statuses) == 'danger') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $this->getStatusText($customer->order_statuses) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>Primera: {{ $customer->first_order_date ? (is_string($customer->first_order_date) ? date('d/m/Y', strtotime($customer->first_order_date)) : $customer->first_order_date->format('d/m/Y')) : 'N/A' }}</div>
                                    <div>Última: {{ $customer->last_order_date ? (is_string($customer->last_order_date) ? date('d/m/Y', strtotime($customer->last_order_date)) : $customer->last_order_date->format('d/m/Y')) : 'N/A' }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                @if($customers->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay clientes</h3>
                        <p class="mt-1 text-sm text-gray-500">No se encontraron clientes con datos registrados.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function filterCustomers() {
            const searchTerm = document.getElementById('customer-search').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;
            const rows = document.querySelectorAll('.customer-row');
            
            rows.forEach(row => {
                const name = row.dataset.name;
                const email = row.dataset.email;
                const status = row.dataset.status;
                
                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;
                
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        function sortCustomers() {
            const sortBy = document.getElementById('sort-by').value;
            const table = document.getElementById('customers-table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('.customer-row'));
            
            rows.sort((a, b) => {
                let aVal, bVal;
                
                switch(sortBy) {
                    case 'total_amount':
                        aVal = parseFloat(a.dataset.totalAmount);
                        bVal = parseFloat(b.dataset.totalAmount);
                        return bVal - aVal; // Descending
                    case 'total_orders':
                        aVal = parseInt(a.dataset.totalOrders);
                        bVal = parseInt(b.dataset.totalOrders);
                        return bVal - aVal; // Descending
                    case 'customer_name':
                        aVal = a.dataset.customerName;
                        bVal = b.dataset.customerName;
                        return aVal.localeCompare(bVal); // Ascending
                    case 'last_order_date':
                        aVal = parseInt(a.dataset.lastOrder);
                        bVal = parseInt(b.dataset.lastOrder);
                        return bVal - aVal; // Descending
                    default:
                        return 0;
                }
            });
            
            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>
</x-filament-panels::page>
