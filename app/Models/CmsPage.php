<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use App\Traits\Filterable;

class CmsPage extends Model
{
    use HasUuid, Auditable, Filterable, SoftDeletes;

    protected $table = 'cms_pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'status',
        'template',
    ];
}
