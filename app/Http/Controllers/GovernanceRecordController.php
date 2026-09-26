<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGovernanceRecordRequest;
use App\Http\Requests\UpdateGovernanceRecordRequest;
use App\Models\GovernanceDocument;
use App\Models\GovernanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GovernanceRecordController extends Controller
{
    /**
     * List governance records with optional filters.
     */
    public function index(Request $request, GovernanceDocument $governanceDocument): JsonResponse
    {
        $query = GovernanceRecord::query()->with([
            'document:id,title,reference_no,status',
            'recorder:id,name,email',
        ]);

        $query->where('governance_document_id', $governanceDocument->id);

        if ($request->filled('action')) {
            $query->where('action', (string) $request->string('action'));
        }

        $records = $query
            ->latestFirst()
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json($records);
    }

    /**
     * Create a governance record.
     */
    public function store(StoreGovernanceRecordRequest $request, GovernanceDocument $governanceDocument): JsonResponse
    {
        $validated = $request->validated();

        $validated['governance_document_id'] = $governanceDocument->id;
        $validated['recorded_at'] = $validated['recorded_at'] ?? now();
        $validated['recorded_by'] = $validated['recorded_by'] ?? $request->user()?->id;

        $record = GovernanceRecord::create($validated);

        return response()->json(
            $record->load([
                'document:id,title,reference_no,status',
                'recorder:id,name,email',
            ]),
            201
        );
    }

    /**
     * Show one governance record.
     */
    public function show(GovernanceRecord $governanceRecord): JsonResponse
    {
        return response()->json(
            $governanceRecord->load([
                'document:id,title,reference_no,status',
                'recorder:id,name,email',
            ])
        );
    }

    /**
     * Update a governance record.
     */
    public function update(UpdateGovernanceRecordRequest $request, GovernanceRecord $governanceRecord): JsonResponse
    {
        $validated = $request->validated();

        $governanceRecord->update($validated);

        return response()->json(
            $governanceRecord->fresh()->load([
                'document:id,title,reference_no,status',
                'recorder:id,name,email',
            ])
        );
    }

    /**
     * Delete a governance record.
     */
    public function destroy(GovernanceRecord $governanceRecord): JsonResponse
    {
        $governanceRecord->delete();

        return response()->json(status: 204);
    }
}
