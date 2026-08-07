<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signature_file' => ['nullable', 'image', 'max:2048'],
            'signature_data' => ['nullable', 'string'],
            'cachet_file' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
