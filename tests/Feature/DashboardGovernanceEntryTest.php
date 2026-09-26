<?php

namespace Tests\Feature;

use App\Models\GovernanceDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardGovernanceEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_read_only_governance_view_for_verified_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Governance Dashboard')
            ->assertSee('Overview guide')
            ->assertSee('This overview is read-only.')
            ->assertDontSee('Add Governance Document')
            ->assertDontSee('Save Document')
            ->assertDontSee('Add Governance Record');
    }

    public function test_dashboard_supports_search_and_filters_for_documents(): void
    {
        $user = User::factory()->create();

        GovernanceDocument::factory()->create([
            'title' => 'Driver Safety Policy',
            'reference_no' => 'DSP-001',
            'document_type' => 'policy',
            'category' => 'safety',
            'status' => 'active',
            'uploaded_by' => $user->id,
        ]);

        GovernanceDocument::factory()->create([
            'title' => 'Finance Review Procedure',
            'reference_no' => 'FRP-022',
            'document_type' => 'resolution',
            'category' => 'finance',
            'status' => 'draft',
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/dashboard?q=Driver&category=safety&status=active')
            ->assertOk()
            ->assertSee('Driver Safety Policy')
            ->assertSee('Search')
            ->assertSee('Category')
            ->assertSee('Status')
            ->assertDontSee('Finance Review Procedure');
    }

    public function test_dashboard_shows_resolution_register_with_related_minutes_and_supporting_documents(): void
    {
        $user = User::factory()->create();
        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Board Minutes 2026',
            'reference_no' => 'MIN-2026-MIN',
            'document_type' => 'minutes',
            'category' => 'board',
            'status' => 'active',
            'file_path' => 'governance-documents/board-minutes-2026.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Board Resolution 2026',
            'reference_no' => 'RES-2026-RES',
            'document_type' => 'resolution',
            'category' => 'board',
            'status' => 'active',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/board-resolution-2026.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $supportingDocument = GovernanceDocument::factory()->create([
            'title' => 'Board Support Memo',
            'reference_no' => 'COM-2026-SUP',
            'document_type' => 'communication',
            'category' => 'board',
            'status' => 'active',
            'related_resolution_id' => $resolution->id,
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/board-support-memo.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($supportingDocument->file_path, '%PDF-1.4\n');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Resolution Report')
            ->assertSee('Consolidated Resolution Report')
            ->assertSee('Board Support Memo')
            ->assertSee('RES-2026-RES')
            ->assertSee('MIN-2026-MIN')
            ->assertSee('COM-2026-SUP')
            ->assertSee(route('admin.governance-documents.viewer', $resolution))
            ->assertSee(route('admin.governance-documents.viewer', $minute))
            ->assertSee(route('admin.governance-documents.viewer', $supportingDocument));
    }

    public function test_dashboard_shows_consolidated_report_when_filtering_for_resolution_documents(): void
    {
        $user = User::factory()->create();
        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Finance Minutes',
            'reference_no' => 'MIN-2026-FIN',
            'document_type' => 'minutes',
            'category' => 'finance',
            'status' => 'active',
            'file_path' => 'governance-documents/finance-minutes.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Finance Resolution',
            'reference_no' => 'RES-2026-FIN',
            'document_type' => 'resolution',
            'category' => 'finance',
            'status' => 'active',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/finance-resolution.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $this->actingAs($user)
            ->get('/dashboard?document_type=resolution')
            ->assertOk()
            ->assertSee('Consolidated Resolution Report')
            ->assertSee('Finance Resolution')
            ->assertSee('MIN-2026-FIN');
    }

    public function test_filtered_resolution_results_appear_in_consolidated_report(): void
    {
        $user = User::factory()->create();
        Storage::fake('private');

        $boardMinute = GovernanceDocument::factory()->create([
            'title' => 'Board Minutes 2026',
            'reference_no' => 'MIN-2026-001',
            'document_type' => 'minutes',
            'category' => 'board',
            'status' => 'active',
            'file_path' => 'governance-documents/board-minutes-2026.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($boardMinute->file_path, '%PDF-1.4\n');

        GovernanceDocument::factory()->create([
            'title' => 'Board Resolution 2026',
            'reference_no' => 'RES-2026-001',
            'document_type' => 'resolution',
            'category' => 'board',
            'status' => 'active',
            'related_minute_id' => $boardMinute->id,
            'file_path' => 'governance-documents/board-resolution-2026.pdf',
            'uploaded_by' => $user->id,
        ]);

        $financeMinute = GovernanceDocument::factory()->create([
            'title' => 'Finance Minutes 2026',
            'reference_no' => 'MIN-2026-002',
            'document_type' => 'minutes',
            'category' => 'finance',
            'status' => 'active',
            'file_path' => 'governance-documents/finance-minutes-2026.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($financeMinute->file_path, '%PDF-1.4\n');

        GovernanceDocument::factory()->create([
            'title' => 'Finance Resolution 2026',
            'reference_no' => 'RES-2026-002',
            'document_type' => 'resolution',
            'category' => 'finance',
            'status' => 'active',
            'related_minute_id' => $financeMinute->id,
            'file_path' => 'governance-documents/finance-resolution-2026.pdf',
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/dashboard?category=board&document_type=resolution')
            ->assertOk()
            ->assertSee('Consolidated Resolution Report')
            ->assertSee('Board Resolution 2026')
            ->assertDontSee('Finance Resolution 2026');
    }

    public function test_consolidated_report_keeps_resolution_when_private_file_is_missing(): void
    {
        $user = User::factory()->create();
        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Board Minutes 2026',
            'reference_no' => 'MIN-2026-001',
            'document_type' => 'minutes',
            'category' => 'board',
            'status' => 'active',
            'file_path' => 'governance-documents/board-minutes-2026.pdf',
            'uploaded_by' => $user->id,
        ]);

        GovernanceDocument::factory()->create([
            'title' => 'Board Resolution 2026',
            'reference_no' => 'RES-2026-001',
            'document_type' => 'resolution',
            'category' => 'board',
            'status' => 'active',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/board-resolution-2026.pdf',
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/dashboard?document_type=resolution')
            ->assertOk()
            ->assertSee('Consolidated Resolution Report')
            ->assertSee('Board Resolution 2026');
    }

    public function test_next_reference_number_uses_last_generated_sequence_for_the_same_type(): void
    {
        GovernanceDocument::factory()->create([
            'document_type' => 'minutes',
            'reference_no' => 'MIN-' . now()->format('Y') . '-010',
        ]);

        GovernanceDocument::factory()->create([
            'document_type' => 'minutes',
            'reference_no' => 'MIN-' . now()->format('Y') . '-011',
        ]);

        $this->assertSame(
            'MIN-' . now()->format('Y') . '-012',
            GovernanceDocument::nextReferenceNumber('minutes')
        );
    }

    public function test_consolidated_report_keeps_resolution_title_when_resolution_has_no_reference_number(): void
    {
        $user = User::factory()->create();
        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Board Minutes 2026',
            'reference_no' => 'MIN-2026-001',
            'document_type' => 'minutes',
            'category' => 'board',
            'status' => 'active',
            'file_path' => 'governance-documents/board-minutes-2026.pdf',
            'uploaded_by' => $user->id,
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        GovernanceDocument::factory()->create([
            'title' => 'Board Resolution 2026',
            'reference_no' => 'RES-2026-001',
            'document_type' => 'resolution',
            'category' => 'board',
            'status' => 'active',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/board-resolution-2026.pdf',
            'uploaded_by' => $user->id,
        ]);

        GovernanceDocument::factory()->create([
            'title' => 'Second Board Resolution',
            'reference_no' => null,
            'document_type' => 'resolution',
            'category' => 'board',
            'status' => 'active',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/second-board-resolution.pdf',
            'uploaded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/dashboard?document_type=resolution')
            ->assertOk()
            ->assertSee('Second Board Resolution')
            ->assertSee('Board Resolution 2026')
            ->assertDontSee('Board Minutes 2026');
    }

    public function test_verified_user_cannot_submit_document_without_required_file_and_metadata(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/admin/dashboard')
            ->post('/dashboard/governance-documents', [
                'title' => 'Driver Conduct Policy',
                'document_type' => 'policy',
                'status' => 'draft',
                'category' => 'compliance',
            ]);

        $response->assertSessionHasErrors(['document', 'version', 'notes']);
        $response->assertStatus(302);

        $this->assertDatabaseMissing('governance_documents', [
            'title' => 'Driver Conduct Policy',
            'uploaded_by' => $user->id,
        ]);
    }

    public function test_verified_user_can_submit_document_entry_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/dashboard/governance-documents', [
                'title' => 'Driver Conduct Policy',
                'document_type' => 'policy',
                'status' => 'draft',
                'category' => 'compliance',
                'version' => 1,
                'issued_at' => now()->toDateString(),
                'effective_at' => now()->toDateString(),
                'notes' => 'Required operational policy for staff compliance review.',
                'document' => UploadedFile::fake()->create('driver-conduct-policy.pdf', 1024, 'application/pdf'),
            ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('governance_documents', [
            'title' => 'Driver Conduct Policy',
            'document_type' => 'policy',
            'category' => 'compliance',
            'status' => 'draft',
            'reference_no' => 'POL-' . now()->format('Y') . '-001',
            'uploaded_by' => $user->id,
        ]);

        $document = GovernanceDocument::query()->where('title', 'Driver Conduct Policy')->firstOrFail();

        $this->assertDatabaseHas('governance_records', [
            'governance_document_id' => $document->id,
            'action' => 'created',
            'recorded_by' => $user->id,
        ]);
    }

    public function test_verified_user_can_submit_record_entry_form(): void
    {
        $user = User::factory()->create();

        $document = GovernanceDocument::factory()->create([
            'uploaded_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->post('/dashboard/governance-records', [
                'governance_document_id' => $document->id,
                'action' => 'reviewed',
                'remarks' => 'Reviewed during monthly compliance meeting.',
            ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('governance_records', [
            'governance_document_id' => $document->id,
            'action' => 'reviewed',
            'recorded_by' => $user->id,
        ]);
    }

    public function test_verified_user_can_upload_document_file(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/dashboard/governance-documents', [
                'title' => 'Remote Work Policy',
                'document_type' => 'policy',
                'status' => 'draft',
                'category' => 'hr',
                'version' => 1,
                'issued_at' => now()->toDateString(),
                'effective_at' => now()->toDateString(),
                'notes' => 'Working policy update for remote staff operations.',
                'document' => UploadedFile::fake()->create('remote-work-policy.pdf', 1024, 'application/pdf'),
            ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('governance_documents', [
            'title' => 'Remote Work Policy',
            'document_type' => 'policy',
            'category' => 'hr',
            'uploaded_by' => $user->id,
        ]);

        $document = GovernanceDocument::query()->where('title', 'Remote Work Policy')->firstOrFail();

        $this->assertNotNull($document->file_path);
        $this->assertStringContainsString('governance-documents', $document->file_path);
    }
}
