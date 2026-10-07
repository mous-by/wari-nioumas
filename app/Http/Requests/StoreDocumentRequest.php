<?php

namespace App\Http\Requests;

use App\Models\Chauffeur;
use App\Models\Document;
use App\Models\Vehicule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documentable_type' => ['required', Rule::in(['vehicule', 'chauffeur'])],
            'documentable_id' => ['required', 'integer'],
            'type_document' => ['required', 'string', 'max:50'],
            'libelle_autre' => ['required_if:type_document,autre', 'nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:100'],
            'date_etablissement' => ['nullable', 'date'],
            'date_expiration' => ['required_unless:type_document,autre', 'nullable', 'date'],
            'observations' => ['nullable', 'string', 'max:1000'],
            'fichier' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * Le propriétaire doit exister, et le type choisi doit être proposé pour
     * ce genre de propriétaire (ex. "permis_conduite" uniquement pour un
     * chauffeur, pas pour un véhicule).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = $this->input('documentable_type');
            $id = $this->input('documentable_id');

            $modele = $type === 'vehicule' ? Vehicule::class : Chauffeur::class;

            if ($id && ! $modele::whereKey($id)->exists()) {
                $validator->errors()->add('documentable_id', "Ce propriétaire n'existe pas.");

                return;
            }

            $typeDocument = $this->input('type_document');
            if ($typeDocument && $modele && ! array_key_exists($typeDocument, Document::typesPour($modele))) {
                $validator->errors()->add('type_document', 'Ce type de document ne correspond pas au propriétaire choisi.');
            }
        });
    }
}
