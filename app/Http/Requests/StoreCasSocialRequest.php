<?php

namespace App\Http\Requests;

use App\Models\CasSocial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCasSocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'personnel_id' => ['required', 'exists:personnels,id'],
            'type_cas_social_id' => ['required', 'exists:type_cas_sociaux,id'],
            'date_cas' => ['required', 'date'],
            'date_demande' => ['required', 'date'],
            'motif' => ['required', 'string'],
            'observations' => ['nullable', 'string'],
            'montant_demande' => ['required', 'numeric', 'min:0'],
            'mode_paiement' => ['nullable', Rule::in(array_keys(CasSocial::MODES_PAIEMENT))],
        ];
    }
}
