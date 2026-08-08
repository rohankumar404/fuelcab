<x-filament-panels::page>
    <div class="space-y-8">

        {{-- ────────────────────────────────────── Orders Report ────────────────────────────────────── --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-primary-600" />
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Orders Report</h2>
                </div>
            </div>

            <div class="p-6">
                {{-- Filters --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">From Date</label>
                        <input
                            type="date"
                            wire:model.live="orderFrom"
                            wire:change="refreshSummaries"
                            value="{{ $orderFrom }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">To Date</label>
                        <input
                            type="date"
                            wire:model.live="orderTo"
                            wire:change="refreshSummaries"
                            value="{{ $orderTo }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <select
                            wire:model.live="orderStatus"
                            wire:change="refreshSummaries"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="accepted">Accepted</option>
                            <option value="assigned">Assigned</option>
                            <option value="out_for_delivery">Out for Delivery</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="mt-5 grid grid-cols-2 gap-4">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Orders</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($orderSummary['total'] ?? 0) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Revenue</p>
                        <p class="mt-1 text-2xl font-bold text-success-600">
                            ₹{{ number_format((float)($orderSummary['revenue'] ?? 0), 0) }}
                        </p>
                    </div>
                </div>

                {{-- Export button --}}
                <div class="mt-5">
                    <button
                        wire:click="exportOrders"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-900"
                        wire:loading.attr="disabled"
                    >
                        <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                        <span wire:loading.remove wire:target="exportOrders">Export Orders CSV</span>
                        <span wire:loading wire:target="exportOrders">Generating…</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ────────────────────────────────────── Settlements Report ────────────────────────────────────── --}}
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-banknotes class="h-5 w-5 text-success-600" />
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">Settlement Report</h2>
                </div>
            </div>

            <div class="p-6">
                {{-- Filters --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">From Date</label>
                        <input
                            type="date"
                            wire:model.live="settlementFrom"
                            wire:change="refreshSummaries"
                            value="{{ $settlementFrom }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">To Date</label>
                        <input
                            type="date"
                            wire:model.live="settlementTo"
                            wire:change="refreshSummaries"
                            value="{{ $settlementTo }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                        <select
                            wire:model.live="settlementStatus"
                            wire:change="refreshSummaries"
                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="processed">Processed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Settlements</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($settlementSummary['count'] ?? 0) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Gross Sales</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                            ₹{{ number_format((float)($settlementSummary['gross'] ?? 0), 0) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Commission</p>
                        <p class="mt-1 text-2xl font-bold text-danger-600">
                            ₹{{ number_format((float)($settlementSummary['commission'] ?? 0), 0) }}
                        </p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Net Settled</p>
                        <p class="mt-1 text-2xl font-bold text-success-600">
                            ₹{{ number_format((float)($settlementSummary['net'] ?? 0), 0) }}
                        </p>
                    </div>
                </div>

                {{-- Export button --}}
                <div class="mt-5">
                    <button
                        wire:click="exportSettlements"
                        class="inline-flex items-center gap-2 rounded-lg bg-success-600 px-4 py-2 text-sm font-semibold text-white hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-success-500 focus:ring-offset-2 disabled:opacity-50 dark:focus:ring-offset-gray-900"
                        wire:loading.attr="disabled"
                    >
                        <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                        <span wire:loading.remove wire:target="exportSettlements">Export Settlements CSV</span>
                        <span wire:loading wire:target="exportSettlements">Generating…</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
