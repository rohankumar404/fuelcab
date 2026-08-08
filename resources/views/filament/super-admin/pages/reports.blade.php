<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div class="fi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm font-medium text-gray-500">Total Orders</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                    {{ number_format($summary['total_orders'] ?? 0) }}
                </p>
            </div>

            <div class="fi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm font-medium text-gray-500">Total Sales (₹)</p>
                <p class="mt-1 text-2xl font-bold text-success-600">
                    ₹{{ number_format((float)($summary['total_revenue'] ?? 0), 2) }}
                </p>
            </div>

            <div class="fi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm font-medium text-gray-500">Commission Earned (₹)</p>
                <p class="mt-1 text-2xl font-bold text-primary-600">
                    ₹{{ number_format((float)($summary['total_commission'] ?? 0), 2) }}
                </p>
            </div>

            <div class="fi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm font-medium text-gray-500">Total Payouts (₹)</p>
                <p class="mt-1 text-2xl font-bold text-danger-600">
                    ₹{{ number_format((float)($summary['settled_amount'] ?? 0), 2) }}
                </p>
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Filter Report Duration</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">From Date</label>
                    <input
                        type="date"
                        wire:model.live="from"
                        wire:change="refreshSummary"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">To Date</label>
                    <input
                        type="date"
                        wire:model.live="to"
                        wire:change="refreshSummary"
                        class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>
            </div>
        </div>

        {{-- Download Reports Section --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Commerce Reports --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 space-y-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-shopping-bag class="w-5 h-5 text-primary-500" />
                    Commerce Exports
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Export orders matching system-wide direct transactions and marketplace transactions.
                </p>
                <div class="flex flex-col gap-2">
                    <button
                        wire:click="exportDirectOrders"
                        class="inline-flex justify-center items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                        Export Direct Orders CSV
                    </button>
                    <button
                        wire:click="exportMarketplaceOrders"
                        class="inline-flex justify-center items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                        Export Marketplace Orders CSV
                    </button>
                </div>
            </div>

            {{-- System & Finance Reports --}}
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 space-y-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-success-500" />
                    Finance & User Logs
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Export settlements records, commission payments, and the list of registered users.
                </p>
                <div class="flex flex-col gap-2">
                    <button
                        wire:click="exportSettlements"
                        class="inline-flex justify-center items-center gap-2 rounded-lg bg-success-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500"
                    >
                        <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                        Export Settlements CSV
                    </button>
                    <button
                        wire:click="exportUsers"
                        class="inline-flex justify-center items-center gap-2 rounded-lg bg-success-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500"
                    >
                        <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                        Export Users List CSV
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
