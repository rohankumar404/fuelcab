<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Row 1: Channel Breakdown & Orders by Status --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Sales Channel Stats --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-shopping-bag class="w-5 h-5 text-primary-500" />
                    Sales Channels
                </h3>
                <div class="space-y-4">
                    @forelse($stats['channel_stats'] ?? [] as $channel => $data)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-gray-800">
                            <div>
                                <span class="text-sm font-semibold capitalize text-gray-700 dark:text-gray-300">
                                    {{ str_replace('_', ' ', $channel) }}
                                </span>
                                <p class="text-xs text-gray-400">{{ $data['count'] }} Orders</p>
                            </div>
                            <span class="text-sm font-bold text-success-600">₹{{ number_format($data['revenue'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No channel data available.</p>
                    @endforelse
                </div>
            </div>

            {{-- Orders Status --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-success-500" />
                    Orders by Status
                </h3>
                <div class="space-y-3">
                    @forelse($stats['orders_by_status'] ?? [] as $status => $count)
                        <div class="flex items-center justify-between">
                            <span class="text-sm capitalize text-gray-600 dark:text-gray-400">{{ str_replace('_', ' ', $status) }}</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $count }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No orders yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Row 2: Top Performing Vendors & Products --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Top Vendors --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-building-storefront class="w-5 h-5 text-warning-500" />
                    Top Performing Vendors (Delivered Sales)
                </h3>
                <div class="space-y-4">
                    @forelse($stats['top_vendors'] ?? [] as $vendor)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-gray-800">
                            <div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $vendor['brand_name'] }}</span>
                                <p class="text-xs text-gray-400">{{ $vendor['total_orders'] }} Orders</p>
                            </div>
                            <span class="text-sm font-bold text-success-600">₹{{ number_format($vendor['total_sales'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No vendor sales data available.</p>
                    @endforelse
                </div>
            </div>

            {{-- Top Products --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-fire class="w-5 h-5 text-danger-500" />
                    Top Selling Products
                </h3>
                <div class="space-y-4">
                    @forelse($stats['top_products'] ?? [] as $product)
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2 dark:border-gray-800">
                            <div>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $product['name'] }}</span>
                                <p class="text-xs text-gray-400">{{ number_format($product['quantity_sold'], 2) }} Units Sold</p>
                            </div>
                            <span class="text-sm font-bold text-success-600">₹{{ number_format($product['total_revenue'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No product sales data available.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Row 3: Monthly Revenue History --}}
        @if(!empty($stats['monthly_revenue']))
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-success-500" />
                    Monthly Revenue (Last 6 Months)
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="pb-2 text-left font-medium text-gray-500">Month</th>
                                <th class="pb-2 text-right font-medium text-gray-500">Revenue (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($stats['monthly_revenue'] as $month => $revenue)
                                <tr>
                                    <td class="py-2 text-gray-700 dark:text-gray-300">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                                    </td>
                                    <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">
                                        ₹{{ number_format((float)$revenue, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
