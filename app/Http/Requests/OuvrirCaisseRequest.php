<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OuvrirCaisseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solde_ouverture' => ['required', 'numeric', 'min:0'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
