<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBulletinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'primes' => ['nullable', 'numeric', 'min:0'],
            'retenues' => ['nullable', 'numeric', 'min:0'],
            'statut' => ['required', 'in:brouillon,valide,paye'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
