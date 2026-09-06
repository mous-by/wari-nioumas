<?php

namespace App\Http\Requests;

use App\Models\CasSocialDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCasSocialDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_document' => ['required', Rule::in(array_keys(CasSocialDocument::TYPES))],
            'fichier' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
