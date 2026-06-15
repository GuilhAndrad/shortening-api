<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Url\RedirectUrlAction;
use App\Http\Controllers\Controller;
use App\Models\Url;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectController extends Controller
{
    public function __construct(
        private readonly RedirectUrlAction $redirectUrl,
    ) {}

    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $url = Url::query()->where('code', $code)->firstOrFail();

        if ($url->isExpired()) {
            abort(Response::HTTP_GONE, 'Este link expirou.');
        }

        $destination = $this->redirectUrl->execute($url, $request);

        return redirect()->away($destination);
    }
}