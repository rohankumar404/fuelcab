<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorListingResource\Pages;

use App\Filament\Vendor\Resources\VendorListingResource;
use App\Modules\Vendor\Models\VendorListing;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorListing extends EditRecord
{
    protected static string $resource = VendorListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => auth()->user()?->hasRole('super_admin')),
        ];
    }

    /**
     * Block editing if listing is not in an editable state.
     */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var VendorListing $listing */
        $listing = $this->record;

        if (! $listing->isEditable()) {
            \Filament\Notifications\Notification::make()
                ->title('This listing cannot be edited in its current status.')
                ->warning()
                ->send();

            $this->redirect(VendorListingResource::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Prevent vendor_id tampering from form
        unset($data['vendor_id'], $data['approval_status']);

        if (isset($data['product_images'])) {
            $data['product_images'] = collect($data['product_images'])->pluck('url')->filter()->values()->all();
        }
        if (isset($data['certificate_documents'])) {
            $data['certificate_documents'] = collect($data['certificate_documents'])->pluck('url')->filter()->values()->all();
        }

        return $data;
    }
}
