<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Enums;

enum VendorStatus: string
{
    case Pending     = 'pending';
    case UnderReview = 'under_review';
    case Approved    = 'approved';
    case Rejected    = 'rejected';
    case Suspended   = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::Pending     => 'Pending Approval',
            self::UnderReview => 'Under Review',
            self::Approved    => 'Approved / Active',
            self::Rejected    => 'Rejected',
            self::Suspended   => 'Suspended',
        };
    }
}
