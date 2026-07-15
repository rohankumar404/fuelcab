<?php

declare(strict_types=1);

namespace App\Enums;

enum ListingStatus: string
{
    case Draft            = 'DRAFT';
    case PendingApproval  = 'PENDING_APPROVAL';
    case Approved         = 'APPROVED';
    case Rejected         = 'REJECTED';
    case Suspended        = 'SUSPENDED';

    public function label(): string
    {
        return match($this) {
            self::Draft           => 'Draft',
            self::PendingApproval => 'Pending Approval',
            self::Approved        => 'Approved',
            self::Rejected        => 'Rejected',
            self::Suspended       => 'Suspended',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft           => 'gray',
            self::PendingApproval => 'warning',
            self::Approved        => 'success',
            self::Rejected        => 'danger',
            self::Suspended       => 'warning',
        };
    }

    /**
     * Vendor can edit a listing when it is in one of these statuses.
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Rejected], true);
    }

    /**
     * Vendor can submit for approval from these statuses.
     */
    public function isSubmittable(): bool
    {
        return in_array($this, [self::Draft, self::Rejected], true);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->all();
    }
}
