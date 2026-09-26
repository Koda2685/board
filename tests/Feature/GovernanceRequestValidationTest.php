<?php

namespace Tests\Feature;

use App\Models\GovernanceDocument;
use App\Models\GovernanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_document_requires_title(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/governance-documents', [
                'status' => 'draft',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_document_requires_document_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/governance-documents', [
                'title' => 'Whistleblower Policy',
                'status' => 'draft',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['document_type']);
    }

    public function test_store_document_rejects_invalid_status(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/governance-documents', [
                'title' => 'Whistleblower Policy',
                'status' => 'invalid-status',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_document_rejects_invalid_category(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/governance-documents', [
                'title' => 'Whistleblower Policy',
                'document_type' => 'policy',
                'status' => 'draft',
                'category' => 'policy',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_store_nested_record_rejects_invalid_action(): void
    {
        $user = User::factory()->create();

        $document = GovernanceDocument::factory()->create([
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->postJson("/governance-documents/{$document->id}/governance-records", [
                'action' => 'not-valid',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['action']);
    }

    public function test_update_record_rejects_unknown_document_id(): void
    {
        $user = User::factory()->create();

        $record = GovernanceRecord::factory()->create([
            'recorded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->patchJson("/governance-records/{$record->id}", [
                'governance_document_id' => 999999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['governance_document_id']);
    }
}
