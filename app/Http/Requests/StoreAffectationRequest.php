<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicule_id' => ['required', 'exists:vehicules,id'],
            'chauffeur_id' => ['required', 'exists:chauffeurs,id'],
            'montant_journalier' => ['required', 'numeric', 'min:0'],
            'periodicite' => ['required', 'in:'.implode(',', \App\Models\Affectation::PERIODICITES)],
            'date_debut' => ['required', 'date'],
            'observations' => ['nullable', 'string'],
        ];
    }
}
