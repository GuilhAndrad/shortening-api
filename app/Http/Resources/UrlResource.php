<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UrlResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'short_url' => url("/{$this->code}"),
            'original_url' => $this->original_url,
            'clicks_count' => $this->clicks_count,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'qr_code_url' => route('urls.qrcode', ['code' => $this->code]),
        ];
    }
}