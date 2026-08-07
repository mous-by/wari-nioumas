<?php

namespace App\Http\Requests;

use App\Models\Depense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicule_id' => ['nullable', 'exists:vehicules,id'],
            'categorie' => ['required', Rule::in(array_keys(Depense::CATEGORIES))],
            'montant' => ['required', 'numeric', 'min:1'],
            'date_depense' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
