<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVersementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chauffeur_id' => ['required', 'exists:chauffeurs,id'],
            'date_versement' => ['required', 'date'],
            'montant' => ['required', 'numeric', 'min:1'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
