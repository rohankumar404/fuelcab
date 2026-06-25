<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'id' => $this->resource['user']->id,
                'name' => $this->resource['user']->name,
                'email' => $this->resource['user']->email,
                'phone' => $this->resource['user']->phone,
                'role_type' => $this->resource['user']->role_type,
                'google_avatar' => $this->resource['user']->google_avatar,
                'email_verified_at' => $this->resource['user']->email_verified_at,
            ],
            'access_token' => $this->resource['token'],
            'token_type' => 'Bearer',
        ];
    }
}
