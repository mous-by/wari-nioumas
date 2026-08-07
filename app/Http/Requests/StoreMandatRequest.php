<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMandatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'periode_mois' => ['required', 'integer', 'between:1,12'],
            'periode_annee' => ['required', 'integer', 'min:2020', 'max:2100'],
            'date_mandat' => ['required', 'date'],
            'banque' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
