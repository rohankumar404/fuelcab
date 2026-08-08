<x-filament-panels::page>
    <div class="space-y-4">

        {{-- Empty State --}}
        @if($notifications->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl bg-white p-12 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <x-heroicon-o-bell-slash class="h-12 w-12 text-gray-300 dark:text-gray-600" />
                <p class="mt-4 text-base font-medium text-gray-500 dark:text-gray-400">No notifications yet.</p>
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">Order updates, approvals, and alerts will appear here.</p>
            </div>
        @else

            {{-- Unread count badge --}}
            @if($unreadCount > 0)
                <div class="flex items-center gap-2 rounded-lg bg-primary-50 px-4 py-2 text-sm text-primary-700 dark:bg-primary-900/20 dark:text-primary-300">
                    <x-heroicon-m-bell-alert class="h-4 w-4" />
                    <span>You have <strong>{{ $unreadCount }}</strong> unread {{ Str::plural('notification', $unreadCount) }}.</span>
                </div>
            @endif

            {{-- Notification list --}}
            <div class="divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-gray-800 dark:bg-gray-900 dark:ring-white/10">
                @foreach($notifications as $notification)
                    @php
                        $data  = $notification->data;
                        $title = $data['title'] ?? $data['message'] ?? 'Notification';
                        $body  = $data['body']  ?? $data['description'] ?? null;
                        $isRead = $notification->read_at !== null;
                    @endphp
                    <div class="flex items-start gap-4 px-5 py-4 {{ $isRead ? '' : 'bg-primary-50/50 dark:bg-primary-900/10' }}">
                        {{-- Unread dot --}}
                        <div class="mt-1 flex-shrink-0">
                            @if(!$isRead)
                                <span class="inline-block h-2 w-2 rounded-full bg-primary-500"></span>
                            @else
                                <span class="inline-block h-2 w-2 rounded-full bg-gray-200 dark:bg-gray-700"></span>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $title }}</p>
                            @if($body)
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $body }}</p>
                            @endif
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        {{-- Mark read action --}}
                        @if(!$isRead)
                            <button
                                wire:click="markRead('{{ $notification->id }}')"
                                class="flex-shrink-0 rounded-md px-2 py-1 text-xs text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-900/20"
                                title="Mark as read"
                            >
                                Mark read
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
