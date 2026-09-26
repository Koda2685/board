<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGovernanceDocumentRequest;
use App\Http\Requests\UpdateGovernanceDocumentRequest;
use App\Models\GovernanceDocument;
use App\Models\GovernanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceDocumentController extends Controller
{
    /**
     * List governance documents with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = GovernanceDocument::query()->with(['uploader:id,name,email']);

        if ($request->boolean('active')) {
            $query->active();
        }

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $search = (string) $request->string('q');

            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        $documents = $query
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json($documents);
    }

    /**
     * Create a governance document.
     */
    public function store(StoreGovernanceDocumentRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['uploaded_by'] = $validated['uploaded_by'] ?? $request->user()?->id;

        $document = GovernanceDocument::create($validated);

        GovernanceRecord::query()->create([
            'governance_document_id' => $document->id,
            'action' => GovernanceRecord::ACTION_CREATED,
            'remarks' => 'Document created.',
            'recorded_at' => now(),
            'recorded_by' => $request->user()?->id ?? $document->uploaded_by,
        ]);

        return response()->json(
            $document->load(['uploader:id,name,email']),
            201
        );
    }

    /**
     * Show one governance document with its records.
     */
    public function show(GovernanceDocument $governanceDocument): JsonResponse
    {
        return response()->json(
            $governanceDocument->load([
                'uploader:id,name,email',
                'records.recorder:id,name,email',
            ])
        );
    }

    /**
     * Update a governance document.
     */
    public function update(UpdateGovernanceDocumentRequest $request, GovernanceDocument $governanceDocument): JsonResponse
    {
        $validated = $request->validated();
        $originalStatus = $governanceDocument->status;

        $governanceDocument->update($validated);

        if (array_key_exists('status', $validated) && $validated['status'] !== $originalStatus) {
            GovernanceRecord::query()->create([
                'governance_document_id' => $governanceDocument->id,
                'action' => GovernanceRecord::actionForDocumentStatus($validated['status'] ?? null),
                'remarks' => sprintf('Status changed from %s to %s.', $originalStatus ?? 'null', $validated['status'] ?? 'null'),
                'recorded_at' => now(),
                'recorded_by' => $request->user()?->id,
            ]);
        }

        return response()->json($governanceDocument->fresh()->load(['uploader:id,name,email']));
    }

    /**
     * Delete a governance document.
     */
    public function destroy(GovernanceDocument $governanceDocument): JsonResponse
    {
        $governanceDocument->delete();

        return response()->json(status: 204);
    }
}
