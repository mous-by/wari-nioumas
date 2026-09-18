<?php

namespace App\Http\Requests;

use App\Models\AttestationVente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttestationVenteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $estVehicule = fn () => $this->input('type_bien') === 'vehicule';

        return [
            'type_bien' => ['required', Rule::in(array_keys(AttestationVente::TYPES_BIEN))],
            'vehicule_id' => ['nullable', 'exists:vehicules,id'],

            'marque' => [Rule::requiredIf($estVehicule), 'nullable', 'string', 'max:255'],
            'modele' => [Rule::requiredIf($estVehicule), 'nullable', 'string', 'max:255'],
            'immatriculation' => ['nullable', 'string', 'max:50'],
            'numero_chassis' => ['nullable', 'string', 'max:100'],
            'annee' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'couleur' => ['nullable', 'string', 'max:100'],
            'autres_caracteristiques' => ['nullable', 'string'],

            'designation' => [Rule::requiredIf(fn () => ! $estVehicule()), 'nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:100'],
            'quantite' => ['nullable', 'integer', 'min:1'],
            'etat' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],

            'vendeur_representant' => ['required', 'string', 'max:255'],
            'vendeur_nina' => ['nullable', 'string', 'max:50'],
            'acheteur_nom' => ['required', 'string', 'max:255'],
            'acheteur_nina' => ['nullable', 'string', 'max:50'],
            'acheteur_adresse' => ['nullable', 'string'],

            'avec_temoins' => ['nullable', 'boolean'],
            'temoin_1_nom' => ['nullable', 'string', 'max:255'],
            'temoin_2_nom' => ['nullable', 'string', 'max:255'],

            'montant_total' => ['required', 'numeric', 'min:0'],
            'montant_paye' => ['nullable', 'numeric', 'min:0'],
            'mode_paiement' => [Rule::requiredIf(fn () => (float) $this->input('montant_paye', 0) > 0), 'nullable', Rule::in(array_keys(AttestationVente::MODES_PAIEMENT))],

            'date_vente' => ['required', 'date'],
            'lieu_vente' => ['required', 'string', 'max:255'],
            'observations' => ['nullable', 'string'],
        ];
    }
}
