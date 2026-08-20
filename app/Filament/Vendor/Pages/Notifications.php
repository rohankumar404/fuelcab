<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class Notifications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Notifications';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.vendor.pages.notifications';

    /** @var Collection<int, DatabaseNotification> */
    public $notifications;

    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->loadNotifications();
    }

    public function loadNotifications(): void
    {
        $user = auth()->user();

        $this->notifications = $user
            ->notifications()
            ->latest()
            ->limit(50)
            ->get();

        $this->unreadCount = $user->unreadNotifications()->count();
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();

        $this->loadNotifications();

        Notification::make()
            ->title('All notifications marked as read.')
            ->success()
            ->send();
    }

    public function markRead(string $id): void
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        $this->loadNotifications();
    }

    public function clearAll(): void
    {
        auth()->user()->notifications()->delete();

        $this->loadNotifications();

        Notification::make()
            ->title('All notifications cleared.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_all_read')
                ->label('Mark All Read')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->unreadCount > 0)
                ->action('markAllRead'),

            Action::make('clear_all')
                ->label('Clear All')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => $this->notifications->isNotEmpty())
                ->action('clearAll'),
        ];
    }
}
