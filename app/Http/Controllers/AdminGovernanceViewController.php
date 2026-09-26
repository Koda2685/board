<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGovernanceDocumentRequest;
use App\Models\GovernanceDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminGovernanceViewController extends Controller
{
    /**
     * Display a read-only list of governance documents.
     */
    public function index(Request $request): View
    {
        $query = GovernanceDocument::query()
            ->with(['uploader:id,name,email'])
            ->withCount('records');

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->string('document_type'));
        }

        $documents = $query
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->appends($request->query());

        return view('admin.governance-documents.index', [
            'documents' => $documents,
        ]);
    }

    /**
     * Display one governance document and related records in read-only mode.
     */
    public function show(GovernanceDocument $governanceDocument): View
    {
        $governanceDocument->load([
            'uploader:id,name,email',
            'records' => fn ($query) => $query
                ->with(['recorder:id,name,email'])
                ->latestFirst(),
        ]);

        return view('admin.governance-documents.show', [
            'document' => $governanceDocument,
        ]);
    }

    public function download(GovernanceDocument $governanceDocument)
    {
        if (blank($governanceDocument->file_path)) {
            return response()->view('governance-documents.unavailable', [
                'document' => $governanceDocument,
                'message' => 'This attachment is currently unavailable.',
            ], 200);
        }

        if (! Storage::disk('private')->exists($governanceDocument->file_path)) {
            return response()->view('governance-documents.unavailable', [
                'document' => $governanceDocument,
                'message' => 'This attachment is currently unavailable.',
            ], 200);
        }

        $fileName = basename($governanceDocument->file_path);

        return response()->file(
            Storage::disk('private')->path($governanceDocument->file_path),
            [
                'Content-Disposition' => 'inline; filename="'.$fileName.'"',
                'Content-Type' => mime_content_type(Storage::disk('private')->path($governanceDocument->file_path)),
            ]
        );
    }

    public function viewer(GovernanceDocument $governanceDocument)
    {
        if (blank($governanceDocument->file_path)) {
            return view('governance-documents.unavailable', [
                'document' => $governanceDocument,
                'message' => 'This attachment is currently unavailable.',
            ]);
        }

        if (! Storage::disk('private')->exists($governanceDocument->file_path)) {
            return view('governance-documents.unavailable', [
                'document' => $governanceDocument,
                'message' => 'This attachment is currently unavailable.',
            ]);
        }

        return view('governance-documents.viewer', [
            'document' => $governanceDocument,
            'pdfUrl' => route('admin.governance-documents.download', $governanceDocument),
        ]);
    }

    public function update(UpdateGovernanceDocumentRequest $request, GovernanceDocument $governanceDocument): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('document')) {
            if (filled($governanceDocument->file_path) && Storage::disk('private')->exists($governanceDocument->file_path)) {
                Storage::disk('private')->delete($governanceDocument->file_path);
            }

            $validated['file_path'] = $request->file('document')->store('governance-documents', 'private');
        }

        unset($validated['document']);

        $governanceDocument->update($validated);

        return redirect()->route('admin.governance-documents.show', $governanceDocument)
            ->with('status', 'governance-document-updated');
    }
}
