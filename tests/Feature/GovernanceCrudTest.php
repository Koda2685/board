<?php

namespace Tests\Feature;

use App\Models\GovernanceDocument;
use App\Models\GovernanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class GovernanceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_and_view_document(): void
    {
        $user = User::factory()->create();

        $create = $this->actingAs($user)->postJson('/governance-documents', [
            'title' => 'Conflict of Interest Policy',
            'document_type' => 'policy',
            'status' => 'draft',
            'category' => 'compliance',
            'version' => 1,
            'issued_at' => now()->toDateString(),
            'effective_at' => now()->toDateString(),
            'notes' => 'Required compliance policy for staff declarations.',
            'document' => UploadedFile::fake()->create('conflict-of-interest-policy.pdf', 1024, 'application/pdf'),
        ]);

        $create
            ->assertCreated()
            ->assertJsonPath('title', 'Conflict of Interest Policy')
            ->assertJsonPath('document_type', 'policy')
            ->assertJsonPath('uploaded_by', $user->id);

        $this->assertDatabaseHas('governance_records', [
            'governance_document_id' => $create->json('id'),
            'action' => 'created',
            'recorded_by' => $user->id,
        ]);

        $documentId = (int) $create->json('id');

        $this->actingAs($user)
            ->getJson("/governance-documents/{$documentId}")
            ->assertOk()
            ->assertJsonPath('id', $documentId);
    }

    public function test_authenticated_user_can_create_nested_record_for_document(): void
    {
        $user = User::factory()->create();

        $document = GovernanceDocument::factory()->create([
            'uploaded_by' => $user->id,
        ]);

        $create = $this->actingAs($user)->postJson(
            "/governance-documents/{$document->id}/governance-records",
            [
                'action' => 'reviewed',
                'remarks' => 'Annual compliance check completed.',
            ]
        );

        $create
            ->assertCreated()
            ->assertJsonPath('governance_document_id', $document->id)
            ->assertJsonPath('recorded_by', $user->id)
            ->assertJsonPath('action', 'reviewed');

        $recordId = (int) $create->json('id');

        $this->actingAs($user)
            ->getJson("/governance-records/{$recordId}")
            ->assertOk()
            ->assertJsonPath('id', $recordId);
    }

    public function test_authenticated_user_can_update_and_delete_record(): void
    {
        $user = User::factory()->create();

        $record = GovernanceRecord::factory()->create([
            'recorded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->patchJson("/governance-records/{$record->id}", [
                'action' => 'approved',
            ])
            ->assertOk()
            ->assertJsonPath('action', 'approved');

        $this->actingAs($user)
            ->deleteJson("/governance-records/{$record->id}")
            ->assertNoContent();
    }

    public function test_status_change_creates_a_governance_record(): void
    {
        $user = User::factory()->create();

        $document = GovernanceDocument::factory()->create([
            'status' => 'draft',
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->patchJson("/governance-documents/{$document->id}", [
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'active');

        $this->assertDatabaseHas('governance_records', [
            'governance_document_id' => $document->id,
            'action' => 'activated',
            'recorded_by' => $user->id,
        ]);
    }
}
