<?php

namespace App\Http\Requests;

use App\Models\GovernanceRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGovernanceRecordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'governance_document_id' => ['sometimes', 'required', 'integer', 'exists:governance_documents,id'],
            'action' => ['sometimes', 'required', 'string', Rule::in(GovernanceRecord::allowedActions())],
            'remarks' => ['sometimes', 'nullable', 'string'],
            'recorded_at' => ['sometimes', 'nullable', 'date'],
            'recorded_by' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];
    }
}
