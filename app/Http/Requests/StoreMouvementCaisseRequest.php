<?php

namespace App\Http\Requests;

use App\Models\MouvementCaisse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMouvementCaisseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(MouvementCaisse::TYPES))],
            'libelle' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:1'],
            'date_mouvement' => ['required', 'date'],
        ];
    }
}
