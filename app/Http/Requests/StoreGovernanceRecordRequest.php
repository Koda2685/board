<?php

namespace App\Http\Requests;

use App\Models\GovernanceRecord;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGovernanceRecordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', 'string', Rule::in(GovernanceRecord::allowedActions())],
            'remarks' => ['nullable', 'string'],
            'recorded_at' => ['nullable', 'date'],
            'recorded_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
