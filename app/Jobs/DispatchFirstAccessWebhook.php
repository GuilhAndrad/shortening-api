<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Url;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchFirstAccessWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(
        private readonly Url $url,
    ) {}

    public function handle(): void
    {
        $webhookUrl = config('services.webhook.url');

        if ($webhookUrl === null) {
            return;
        }

        Http::post($webhookUrl, [
            'event' => 'url.first_accessed',
            'code' => $this->url->code,
            'original_url' => $this->url->original_url,
            'accessed_at' => now()->toIso8601String(),
        ])->throw();
    }
}