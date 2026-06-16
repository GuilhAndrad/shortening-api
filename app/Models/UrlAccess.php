<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'url_id',
    'ip_address',
    'user_agent',
    'accessed_at',
])]
class UrlAccess extends Model
{
    public $timestamps = false;
    
    protected function casts(): array
    {
        return [
            'accessed_at' => 'datetime',
        ];
    }

    public function url(): BelongsTo
    {
        return $this->belongsTo(Url::class);
    }
}