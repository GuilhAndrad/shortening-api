<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Url;
use App\Models\User;

class UrlPolicy
{
    public function delete(User $user, Url $url): bool
    {
        return $user->id === $url->user_id;
    }
}