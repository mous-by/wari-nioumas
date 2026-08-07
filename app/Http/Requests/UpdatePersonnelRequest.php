<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonnelRequest extends FormRequest
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
            'telephone' => ['nullable', 'string', 'max:30'],
            'poste' => ['required', 'string', 'max:255'],
            'salaire_base' => ['required', 'numeric', 'min:0'],
            'banque' => ['nullable', 'string', 'max:255'],
            'numero_compte' => ['nullable', 'string', 'max:255'],
            'date_embauche' => ['nullable', 'date'],
            'statut' => ['required', 'in:actif,inactif'],
            'user_id' => ['nullable', 'exists:users,id'],
            'chauffeur_id' => ['nullable', 'exists:chauffeurs,id'],
            'observations' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
