<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\Rules\Phone;

class StoreChauffeurRequest extends FormRequest
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
            'telephone' => ['required', 'string', (new Phone())->country('ML'), 'unique:chauffeurs,telephone'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'nina' => ['required', 'string', 'max:50', 'unique:chauffeurs,nina'],
            'permis_numero' => ['required', 'string', 'max:50'],
            'permis_date_validite' => ['required', 'date'],
            'date_embauche' => ['required', 'date'],
            'statut' => ['required', Rule::in(['actif', 'inactif', 'suspendu'])],
            'observations' => ['nullable', 'string'],
        ];
    }
}
