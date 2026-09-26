<?php

namespace App\Http\Requests;

use App\Models\GovernanceDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGovernanceDocumentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'reference_no' => ['nullable', 'string', 'max:100', 'unique:governance_documents,reference_no'],
            'document_type' => ['required', 'string', Rule::in(['policy', 'regulation', 'minutes', 'resolution', 'communication', 'special_resolution', 'board_reviewed'])],
            'category' => ['required', 'string', Rule::in(GovernanceDocument::allowedCategories())],
            'version' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', Rule::in(['draft', 'active', 'archived', 'expired'])],
            'issued_at' => ['required', 'date'],
            'effective_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:effective_at'],
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,csv,txt', 'max:2048'],
            'notes' => ['required', 'string'],
            'uploaded_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
