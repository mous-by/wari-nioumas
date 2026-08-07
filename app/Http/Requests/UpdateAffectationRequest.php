<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant_journalier' => ['required', 'numeric', 'min:0'],
            'date_debut' => ['required', 'date'],
            'observations' => ['nullable', 'string'],
        ];
    }
}
