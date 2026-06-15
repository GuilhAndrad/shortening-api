<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Url;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function __invoke(string $code): Response
    {
        $url = Url::query()->where('code', $code)->firstOrFail();

        $shortUrl = url("/{$code}");

        return response(
            QrCode::format('svg')->size(300)->generate($shortUrl),
            200,
            ['Content-Type' => 'image/svg+xml'],
        );
    }
}