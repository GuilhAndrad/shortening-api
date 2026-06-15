<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShortenUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'url', 'max:2048'],
            'custom' => [
                'nullable',
                'string',
                'alpha_num',
                'min:6',
                'max:8',
                Rule::unique('urls', 'code'),
            ],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'custom.unique' => 'Este código personalizado já está em uso.',
            'custom.alpha_num' => 'O código personalizado deve conter apenas letras e números.',
        ];
    }
}