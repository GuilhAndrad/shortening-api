<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        $url = $this->route('url');

        return $url->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'original_url' => ['sometimes', 'url', 'max:2048'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after:now'],
        ];
    }
}