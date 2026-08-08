<?php

namespace App\Http\Requests;

use App\Rules\MalianPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'telephone' => ['required', 'string', new MalianPhone(), 'unique:chauffeurs,telephone'],
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
