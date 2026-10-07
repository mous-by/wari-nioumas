<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_document' => ['required', 'string', 'max:50'],
            'libelle_autre' => ['required_if:type_document,autre', 'nullable', 'string', 'max:255'],
            'numero' => ['nullable', 'string', 'max:100'],
            'date_etablissement' => ['nullable', 'date'],
            'date_expiration' => ['required_unless:type_document,autre', 'nullable', 'date'],
            'observations' => ['nullable', 'string', 'max:1000'],
            'fichier' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Document $document */
            $document = $this->route('document');
            $typeDocument = $this->input('type_document');

            if ($typeDocument && ! array_key_exists($typeDocument, Document::typesPour($document->documentable_type))) {
                $validator->errors()->add('type_document', 'Ce type de document ne correspond pas au propriétaire de ce document.');
            }
        });
    }
}
