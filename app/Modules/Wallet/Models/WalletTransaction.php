<?php

declare(strict_types=1);

namespace App\Modules\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
use App\Traits\HasTenantScope;
class WalletTransaction extends Model
{
    use HasUuid,HasTenantScope;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
