<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class VendorSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.vendor.pages.vendor-settings';

    public ?array $settingsData = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'name'                        => $user?->name,
            'email'                       => $user?->email,
            'phone'                       => $user?->phone,
            'notify_new_order'            => true,
            'notify_quote_request'        => true,
            'notify_listing_status'       => true,
            'notify_settlement'           => true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Details')->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Your Name')
                        ->required(),

                    Forms\Components\TextInput::make('email')
                        ->label('Email Address')
                        ->email()
                        ->disabled()
                        ->helperText('Contact support to change your email address.'),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->nullable(),
                ])->columns(3),

                Forms\Components\Section::make('Change Password')->schema([
                    Forms\Components\TextInput::make('current_password')
                        ->label('Current Password')
                        ->password()
                        ->nullable()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('new_password')
                        ->label('New Password')
                        ->password()
                        ->minLength(8)
                        ->nullable()
                        ->dehydrated(false),

                    Forms\Components\TextInput::make('confirm_password')
                        ->label('Confirm New Password')
                        ->password()
                        ->same('new_password')
                        ->nullable()
                        ->dehydrated(false),
                ])->columns(3),

                Forms\Components\Section::make('Notification Preferences')->schema([
                    Forms\Components\Toggle::make('notify_new_order')
                        ->label('New Order Received')
                        ->default(true),

                    Forms\Components\Toggle::make('notify_quote_request')
                        ->label('New Quote Request')
                        ->default(true),

                    Forms\Components\Toggle::make('notify_listing_status')
                        ->label('Listing Approval / Rejection')
                        ->default(true),

                    Forms\Components\Toggle::make('notify_settlement')
                        ->label('Settlement Processed')
                        ->default(true),
                ])->columns(2),
            ])
            ->statePath('settingsData');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        // Update allowed user fields
        $user->update([
            'name'  => $data['name'],
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }
}
