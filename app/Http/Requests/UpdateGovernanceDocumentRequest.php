<?php

namespace App\Http\Requests;

use App\Models\GovernanceDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGovernanceDocumentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var GovernanceDocument|null $governanceDocument */
        $governanceDocument = $this->route('governanceDocument') ?? $this->route('governance_document');
        $ignoreId = $governanceDocument?->id;

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'reference_no' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('governance_documents', 'reference_no')->ignore($ignoreId),
            ],
            'document_type' => ['sometimes', 'required', 'string', Rule::in(['policy', 'regulation', 'minutes', 'resolution', 'communication', 'special_resolution', 'board_reviewed'])],
            'category' => ['sometimes', 'nullable', 'string', Rule::in(GovernanceDocument::allowedCategories())],
            'version' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['draft', 'active', 'archived', 'expired'])],
            'issued_at' => ['sometimes', 'nullable', 'date'],
            'effective_at' => ['sometimes', 'nullable', 'date'],
            'expires_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:effective_at'],
            'file_path' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'document' => ['sometimes', 'nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg,csv,txt', 'max:2048'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'uploaded_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }
}
