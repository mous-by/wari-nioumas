<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // Pas de montant à la création d'une affectation "voyage" : chaque
            // voyage a le sien, ajouté séparément (StoreVoyageRequest).
            'montant_journalier' => [Rule::requiredIf(fn () => $this->input('periodicite') !== 'voyage'), 'nullable', 'numeric', 'min:0'],
            'periodicite' => ['required', 'in:'.implode(',', \App\Models\Affectation::PERIODICITES)],
            'date_debut' => ['required', 'date'],
            'observations' => ['nullable', 'string'],
        ];
    }
}
