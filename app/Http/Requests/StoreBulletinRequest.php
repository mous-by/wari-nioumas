<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulletinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personnel_id' => ['required', 'exists:personnels,id'],
            'periode_mois' => ['required', 'integer', 'between:1,12'],
            'periode_annee' => ['required', 'integer', 'min:2020', 'max:2100'],
            'primes' => ['nullable', 'numeric', 'min:0'],
            'retenues' => ['nullable', 'numeric', 'min:0'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
