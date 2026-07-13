<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasUuid;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use App\Enums\UserRole;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasUuid;

    /**
     * The guard name for Spatie permissions.
     *
     * @var string
     */
    protected string $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'google_id',
        'google_token',
        'google_avatar',
        'role_type',
        'status',
        'email_verified_at',
        'company_id',
        'vendor_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_type' => \App\Enums\UserRole::class,
        ];
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Vendor\Models\Vendor::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Authorize panel access dynamically based on roles and approval state.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        if ($panelId === 'super-admin') {
            return $this->hasRole(UserRole::SuperAdmin->value);
        }

        if ($panelId === 'operations') {
            return $this->hasRole(UserRole::OperationsTeam->value);
        }

        if ($panelId === 'vendor') {
            if (! $this->hasAnyRole([
                UserRole::VendorAdmin->value,
                UserRole::VendorStaff->value,
            ])) {
                return false;
            }

            if (! $this->vendor_id) {
                return false;
            }

            // Load vendor relation to check approval status
            $vendor = $this->vendor;
            return $vendor && $vendor->status === \App\Modules\Vendor\Enums\VendorStatus::Approved;
        }

        return false;
    }
}
