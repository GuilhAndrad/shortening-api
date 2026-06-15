<?php

declare(strict_types=1);

namespace App\Actions\Url;

use App\Models\Url;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ShortenUrlAction
{
    private const CODE_LENGTH = 7;
    private const MAX_RETRIES = 5;

    public function execute(
        string $originalUrl,
        ?User $user,
        ?string $customCode = null,
        ?string $expiresAt = null,
    ): Url {
        if ($user !== null) {
            $this->ensureNotDuplicated($user, $originalUrl);
        }

        $code = $customCode ?? $this->generateUniqueCode();

        return Url::create([
            'user_id' => $user?->id,
            'code' => $code,
            'original_url' => $originalUrl,
            'expires_at' => $expiresAt,
        ]);
    }

    private function ensureNotDuplicated(User $user, string $originalUrl): void
    {
        $exists = Url::query()
            ->where('user_id', $user->id)
            ->where('original_url', $originalUrl)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'url' => ['Você já encurtou esta URL anteriormente.'],
            ]);
        }
    }

    private function generateUniqueCode(): string
    {
        for ($attempt = 0; $attempt < self::MAX_RETRIES; $attempt++) {
            $length = random_int(6, 8);
            $code = Str::random($length);

            if (! Url::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        throw new \RuntimeException('Não foi possível gerar um código único após várias tentativas.');
    }
}
