<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Jobs\DispatchFirstAccessWebhook;
use App\Models\Url;
use Illuminate\Http\Request;

final class RedirectUrlAction
{
    public function execute(Url $url, Request $request): string
    {
        $isFirstAccess = $url->clicks_count === 0;

        $url->accesses()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accessed_at' => now(),
        ]);

        $url->increment('clicks_count');

        if ($isFirstAccess) {
            DispatchFirstAccessWebhook::dispatch($url);
        }

        return $url->original_url;
    }
}