<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehiculeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'immatriculation' => ['required', 'string', 'max:50', 'unique:vehicules,immatriculation'],
            'marque' => ['required', 'string', 'max:100'],
            'modele' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'max:100'],
            'annee' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 1)],
            'etat' => ['required', Rule::in(['actif', 'non_actif', 'vendu', 'garage'])],
            'observations' => ['nullable', 'string'],
        ];
    }
}
