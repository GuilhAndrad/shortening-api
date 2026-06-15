<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Url\ShortenUrlAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ShortenUrlRequest;
use App\Http\Resources\UrlResource;
use Illuminate\Http\JsonResponse;

class ShortenController extends Controller
{
    public function __construct(
        private readonly ShortenUrlAction $shortenUrl,
    ) {}

    public function __invoke(ShortenUrlRequest $request): JsonResponse
    {
        $url = $this->shortenUrl->execute(
            originalUrl: $request->validated('url'),
            user: $request->user(),
            customCode: $request->validated('custom'),
            expiresAt: $request->validated('expires_at'),
        );

        return UrlResource::make($url)
            ->response()
            ->setStatusCode(201);
    }
}