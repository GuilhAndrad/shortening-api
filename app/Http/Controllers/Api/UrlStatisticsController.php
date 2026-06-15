<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Url\GetUrlStatisticsAction;
use App\Http\Controllers\Controller;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UrlStatisticsController extends Controller
{
    public function __construct(
        private readonly GetUrlStatisticsAction $getStatistics,
    ) {}

    public function __invoke(string $code): JsonResponse
    {
        $url = Url::query()->where('code', $code)->firstOrFail();

        if ($url->isExpired()) {
            abort(Response::HTTP_GONE, 'Este link expirou.');
        }

        return response()->json(
            $this->getStatistics->execute($url)
        );
    }
}