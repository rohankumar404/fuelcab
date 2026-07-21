<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\BannerResource\Pages;

use App\Filament\SuperAdmin\Resources\BannerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBanner extends CreateRecord
{
    protected static string $resource = BannerResource::class;
}
