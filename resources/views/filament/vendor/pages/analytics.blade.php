<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        {{-- Summary Cards --}}
        <div class="fi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm font-medium text-gray-500">Total Gross Sales</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                ₹{{ number_format((float)($stats['total_gross'] ?? 0), 0) }}
            </p>
        </div>

        <div class="fi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm font-medium text-gray-500">Commission Paid</p>
            <p class="mt-1 text-2xl font-bold text-danger-600">
                ₹{{ number_format((float)($stats['total_commission'] ?? 0), 0) }}
            </p>
        </div>

        <div class="fi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <p class="text-sm font-medium text-gray-500">Net Settled</p>
            <p class="mt-1 text-2xl font-bold text-success-600">
                ₹{{ number_format((float)($stats['total_net'] ?? 0), 0) }}
            </p>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">

        {{-- Orders by Status --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Orders by Status</h3>
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

        {{-- Listings Summary --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Listings Overview</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Listings</span>
                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $stats['total_listings'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Approved & Active</span>
                    <span class="text-sm font-medium text-success-600">{{ $stats['approved_listings'] ?? 0 }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Low Stock (< 100)</span>
                    <span class="text-sm font-medium {{ ($stats['low_stock'] ?? 0) > 0 ? 'text-danger-600' : 'text-gray-900 dark:text-white' }}">{{ $stats['low_stock'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Revenue --}}
    @if(!empty($stats['monthly_revenue']))
    <div class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Monthly Revenue (Last 6 Months)</h3>
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
                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</td>
                        <td class="py-2 text-right font-semibold text-gray-900 dark:text-white">₹{{ number_format((float)$revenue, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-filament-panels::page>
