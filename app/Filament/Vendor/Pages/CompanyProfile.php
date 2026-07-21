<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Pages;

use App\Models\Company;
use App\Modules\Vendor\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CompanyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Company Profile';

    protected static ?string $navigationLabel = 'Company Profile';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.vendor.pages.company-profile';

    public ?array $vendorData = [];

    public function mount(): void
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            return;
        }

        $this->form->fill([
            'brand_name'         => $vendor->brand_name,
            'contact_email'      => $vendor->contact_email,
            'contact_phone'      => $vendor->contact_phone,
            'website'            => $vendor->website,
            'business_address'   => $vendor->business_address,
            'city'               => $vendor->city,
            'state'              => $vendor->state,
            'pincode'            => $vendor->pincode,
            'description'        => $vendor->description,
            'gst_number'         => $vendor->gst_number,
            'pan_number'         => $vendor->pan_number,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Brand & Contact')->schema([
                    Forms\Components\TextInput::make('brand_name')
                        ->label('Brand / Business Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('contact_email')
                        ->label('Business Contact Email')
                        ->email()
                        ->nullable(),

                    Forms\Components\TextInput::make('contact_phone')
                        ->label('Business Contact Phone')
                        ->tel()
                        ->nullable(),

                    Forms\Components\TextInput::make('website')
                        ->label('Website')
                        ->url()
                        ->placeholder('https://...')
                        ->nullable(),
                ])->columns(2),

                Forms\Components\Section::make('Business Address')->schema([
                    Forms\Components\Textarea::make('business_address')
                        ->label('Address Line')
                        ->rows(2)
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('city')
                        ->label('City')
                        ->nullable(),

                    Forms\Components\TextInput::make('state')
                        ->label('State')
                        ->nullable(),

                    Forms\Components\TextInput::make('pincode')
                        ->label('Pincode')
                        ->nullable(),
                ])->columns(3),

                Forms\Components\Section::make('Tax & Registration')->schema([
                    Forms\Components\TextInput::make('gst_number')
                        ->label('GST Number')
                        ->nullable(),

                    Forms\Components\TextInput::make('pan_number')
                        ->label('PAN Number')
                        ->nullable(),
                ])->columns(2),

                Forms\Components\Section::make('About')->schema([
                    Forms\Components\Textarea::make('description')
                        ->label('Business Description')
                        ->rows(4)
                        ->nullable()
                        ->columnSpanFull(),
                ]),
            ])
            ->statePath('vendorData');
    }

    public function save(): void
    {
        $vendor = $this->getVendor();

        if (! $vendor) {
            Notification::make()->title('Vendor profile not found.')->danger()->send();
            return;
        }

        // SECURITY: Only update fields vendors are allowed to change — not status or commission_rate
        $data = $this->form->getState();
        $allowedFields = [
            'brand_name', 'contact_email', 'contact_phone', 'website',
            'business_address', 'city', 'state', 'pincode',
            'description', 'gst_number', 'pan_number',
        ];

        $vendor->update(array_intersect_key($data, array_flip($allowedFields)));

        Notification::make()
            ->title('Company profile updated.')
            ->success()
            ->send();
    }

    /**
     * SECURITY: Always resolve vendor from authenticated user — never from URL params.
     */
    private function getVendor(): ?Vendor
    {
        $vendorId = auth()->user()?->vendor_id;

        if (! $vendorId) {
            return null;
        }

        return Vendor::find($vendorId);
    }
}
