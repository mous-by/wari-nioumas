<?php

namespace App\Http\Requests;

use App\Models\Accident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicule_id' => ['nullable', 'exists:vehicules,id'],
            'chauffeur_id' => ['nullable', 'exists:chauffeurs,id'],
            'date_accident' => ['required', 'date'],
            'lieu' => ['nullable', 'string', 'max:255'],
            'gravite' => ['required', Rule::in(array_keys(Accident::GRAVITES))],
            'responsabilite' => ['required', Rule::in(array_keys(Accident::RESPONSABILITES))],
            'description' => ['required', 'string', 'max:2000'],
            'cout_reparation' => ['nullable', 'numeric', 'min:0'],
            'decision' => ['nullable', 'string', 'max:2000'],
            'statut' => ['required', Rule::in(array_keys(Accident::STATUTS))],
        ];
    }
}
