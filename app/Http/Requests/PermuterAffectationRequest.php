<?php

namespace App\Http\Requests;

use App\Models\Affectation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PermuterAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'chauffeur_1_id' => ['required', 'exists:chauffeurs,id', 'different:chauffeur_2_id'],
            'chauffeur_2_id' => ['required', 'exists:chauffeurs,id'],
            'date_permutation' => ['required', 'date'],
            'periodicite_1' => ['required', 'in:'.implode(',', Affectation::PERIODICITES)],
            'periodicite_2' => ['required', 'in:'.implode(',', Affectation::PERIODICITES)],
            'montant_1' => [Rule::requiredIf(fn () => $this->input('periodicite_1') !== 'voyage'), 'nullable', 'numeric', 'min:0'],
            'montant_2' => [Rule::requiredIf(fn () => $this->input('periodicite_2') !== 'voyage'), 'nullable', 'numeric', 'min:0'],
            'observations' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach (['chauffeur_1_id', 'chauffeur_2_id'] as $field) {
                $chauffeurId = $this->input($field);
                if (! $chauffeurId) {
                    continue;
                }

                $active = Affectation::where('chauffeur_id', $chauffeurId)->whereNull('date_fin')->exists();
                if (! $active) {
                    $validator->errors()->add($field, "Ce chauffeur n'a aucun véhicule actuellement affecté : impossible de permuter.");
                }
            }
        });
    }
}
