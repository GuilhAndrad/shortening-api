<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUrlRequest;
use App\Http\Resources\UrlResource;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UrlManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $urls = $request->user()
            ->urls()
            ->latest()
            ->paginate(15);

        return UrlResource::collection($urls)->response();
    }

    public function update(UpdateUrlRequest $request, Url $url): JsonResponse
    {
        $url->update($request->validated());

        return UrlResource::make($url)->response();
    }

    public function destroy(Request $request, Url $url): JsonResponse
    {
        $this->authorize('delete', $url);

        $url->delete();

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}