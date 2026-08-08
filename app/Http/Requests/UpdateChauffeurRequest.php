<?php

namespace App\Http\Requests;

use App\Rules\MalianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChauffeurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', new MalianPhone(), Rule::unique('chauffeurs', 'telephone')->ignore($this->route('chauffeur'))],
            'adresse' => ['nullable', 'string', 'max:255'],
            'nina' => ['required', 'string', 'max:50', Rule::unique('chauffeurs', 'nina')->ignore($this->route('chauffeur'))],
            'permis_numero' => ['required', 'string', 'max:50'],
            'permis_date_validite' => ['required', 'date'],
            'date_embauche' => ['required', 'date'],
            'statut' => ['required', Rule::in(['actif', 'inactif', 'suspendu'])],
            'observations' => ['nullable', 'string'],
        ];
    }
}
