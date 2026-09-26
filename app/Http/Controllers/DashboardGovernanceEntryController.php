<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGovernanceDocumentRequest;
use App\Models\GovernanceDocument;
use App\Models\GovernanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DashboardGovernanceEntryController extends Controller
{
    /**
     * Store a governance document from dashboard form.
     */
    public function referencePreview(Request $request)
    {
        $documentType = $request->query('document_type');

        return response()->json([
            'reference_no' => GovernanceDocument::nextReferenceNumber($documentType),
        ]);
    }

    public function storeDocument(StoreGovernanceDocumentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['uploaded_by'] = $validated['uploaded_by'] ?? $request->user()?->id;
        $validated['reference_no'] ??= GovernanceDocument::nextReferenceNumber($validated['document_type'] ?? null);

        if (! $request->hasFile('document')) {
            return back()->withErrors(['document' => 'A document upload is required.'])->withInput();
        }

        $validated['file_path'] = $request->file('document')->store('governance-documents', 'private');
        unset($validated['document']);

        $document = GovernanceDocument::query()->create($validated);

        GovernanceRecord::query()->create([
            'governance_document_id' => $document->id,
            'action' => GovernanceRecord::ACTION_CREATED,
            'remarks' => 'Document created from dashboard.',
            'recorded_at' => now(),
            'recorded_by' => $request->user()?->id,
        ]);

        $redirectRoute = $request->user()?->is_admin ? 'admin.dashboard' : 'dashboard';

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'governance-document-created');
    }

    /**
     * Store a governance record from dashboard form.
     */
    public function storeRecord(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'governance_document_id' => ['required', 'integer', 'exists:governance_documents,id'],
            'action' => ['required', 'string', Rule::in(GovernanceRecord::allowedActions())],
            'remarks' => ['nullable', 'string'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $validated['recorded_at'] = $validated['recorded_at'] ?? now();
        $validated['recorded_by'] = $request->user()?->id;

        GovernanceRecord::query()->create($validated);

        $redirectRoute = $request->user()?->is_admin ? 'admin.dashboard' : 'dashboard';

        return redirect()
            ->route($redirectRoute)
            ->with('status', 'governance-record-created');
    }
}
