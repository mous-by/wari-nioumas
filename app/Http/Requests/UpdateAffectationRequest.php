<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'montant_journalier' => [Rule::requiredIf(fn () => $this->input('periodicite') !== 'voyage'), 'nullable', 'numeric', 'min:0'],
            'periodicite' => ['required', 'in:'.implode(',', \App\Models\Affectation::PERIODICITES)],
            'date_debut' => ['required', 'date'],
            'observations' => ['nullable', 'string'],
        ];
    }
}
