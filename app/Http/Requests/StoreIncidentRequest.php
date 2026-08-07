<?php

namespace App\Http\Requests;

use App\Models\Incident;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncidentRequest extends FormRequest
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
            'date_incident' => ['required', 'date'],
            'type' => ['required', Rule::in(array_keys(Incident::TYPES))],
            'gravite' => ['required', Rule::in(array_keys(Incident::GRAVITES))],
            'description' => ['required', 'string', 'max:2000'],
            'cout' => ['nullable', 'numeric', 'min:0'],
            'decision' => ['nullable', 'string', 'max:2000'],
            'statut' => ['required', Rule::in(array_keys(Incident::STATUTS))],
        ];
    }
}
