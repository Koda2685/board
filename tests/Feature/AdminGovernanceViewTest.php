<?php

namespace Tests\Feature;

use App\Models\GovernanceDocument;
use App\Models\GovernanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminGovernanceViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_governance_pages(): void
    {
        $this->get('/admin/governance-documents')->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_access_admin_governance_pages(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $document = GovernanceDocument::factory()->create();

        $this->actingAs($user)
            ->get('/admin/governance-documents')
            ->assertForbidden();

        $this->actingAs($user)
            ->get("/admin/governance-documents/{$document->id}")
            ->assertForbidden();
    }

    public function test_admin_user_can_view_governance_list_and_document_detail(): void
    {
        $admin = User::factory()->admin()->create();

        $document = GovernanceDocument::factory()->create([
            'title' => 'Road Safety Charter',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance/road-safety-charter.pdf',
        ]);

        GovernanceRecord::factory()->create([
            'governance_document_id' => $document->id,
            'action' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get('/admin/governance-documents')
            ->assertOk()
            ->assertSee('Read-only view')
            ->assertSee('Road Safety Charter')
            ->assertSee('resolution');

        $this->actingAs($admin)
            ->get("/admin/governance-documents/{$document->id}")
            ->assertOk()
            ->assertSee('Document Details')
            ->assertSee('Type:')
            ->assertSee('resolution')
            ->assertSee('Record History')
            ->assertSee('approved')
            ->assertSee('road-safety-charter.pdf')
            ->assertSee(route('admin.governance-documents.viewer', $document))
            ->assertDontSee(route('admin.governance-documents.download', $document))
            ->assertDontSee('Hidden in view-only mode')
            ->assertDontSee('File Path:');
    }

    public function test_admin_user_can_search_and_filter_governance_documents(): void
    {
        $admin = User::factory()->admin()->create();

        GovernanceDocument::factory()->create([
            'title' => 'Driver Safety Policy',
            'reference_no' => 'DSP-001',
            'document_type' => 'policy',
            'category' => 'safety',
            'status' => 'active',
        ]);

        GovernanceDocument::factory()->create([
            'title' => 'Finance Review Procedure',
            'reference_no' => 'FRP-022',
            'document_type' => 'resolution',
            'category' => 'finance',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->get('/admin/governance-documents?q=Driver&category=safety&status=active')
            ->assertOk()
            ->assertSee('Driver Safety Policy')
            ->assertSee('Search')
            ->assertSee('Category')
            ->assertSee('Status')
            ->assertDontSee('Finance Review Procedure');
    }

    public function test_admin_user_can_open_a_governance_document_file_inline_in_browser(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');
        Storage::disk('private')->put('governance-documents/road-safety-charter.pdf', 'test file contents');

        $document = GovernanceDocument::factory()->create([
            'title' => 'Road Safety Charter',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance-documents/road-safety-charter.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.governance-documents.download', $document))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename="road-safety-charter.pdf"');
    }

    public function test_admin_user_can_open_a_governance_document_in_pdf_viewer(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');
        Storage::disk('private')->put('governance-documents/road-safety-charter.pdf', '%PDF-1.4\n');

        $document = GovernanceDocument::factory()->create([
            'title' => 'Road Safety Charter',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance-documents/road-safety-charter.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.governance-documents.viewer', $document))
            ->assertOk()
            ->assertSee('application/pdf')
            ->assertSee(route('admin.governance-documents.download', $document))
            ->assertDontSee('Open raw file');
    }

    public function test_admin_detail_view_hides_attachment_link_when_the_file_is_missing_from_storage(): void
    {
        $admin = User::factory()->admin()->create();

        $document = GovernanceDocument::factory()->create([
            'title' => 'Stale Attachment Record',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance-documents/missing-file.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.governance-documents.show', $document))
            ->assertOk()
            ->assertSee('No attachment uploaded')
            ->assertDontSee(route('admin.governance-documents.viewer', $document));
    }

    public function test_missing_attachment_file_returns_a_user_friendly_unavailable_page_instead_of_a_laravel_404(): void
    {
        $admin = User::factory()->admin()->create();

        $document = GovernanceDocument::factory()->create([
            'title' => 'Missing Attachment Record',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance-documents/missing-file.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.governance-documents.viewer', $document))
            ->assertOk()
            ->assertSee('This attachment is currently unavailable')
            ->assertSee('missing-file.pdf');
    }

    public function test_admin_user_can_replace_a_document_file_and_update_metadata(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');
        $document = GovernanceDocument::factory()->create([
            'title' => 'Original Board Resolution',
            'document_type' => 'resolution',
            'category' => 'board',
            'status' => 'draft',
            'version' => 1,
            'notes' => 'Original draft',
            'file_path' => 'governance-documents/original-board-resolution.pdf',
        ]);
        Storage::disk('private')->put($document->file_path, '%PDF-1.4\n');

        $this->actingAs($admin)
            ->put(route('admin.governance-documents.update', $document), [
                'title' => 'Updated Board Resolution',
                'document_type' => 'resolution',
                'category' => 'board',
                'status' => 'active',
                'version' => 2,
                'notes' => 'Updated version approved',
                'document' => UploadedFile::fake()->create('updated-board-resolution.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.governance-documents.show', $document));

        $document->refresh();

        $this->assertSame('Updated Board Resolution', $document->title);
        $this->assertSame('active', $document->status);
        $this->assertSame(2, $document->version);
        $this->assertNotSame('governance-documents/original-board-resolution.pdf', $document->file_path);
        $this->assertTrue(Storage::disk('private')->exists($document->file_path));
    }

    public function test_admin_user_can_create_board_reviewed_document_type(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Board Reviewed');

        $this->actingAs($admin)
            ->post(route('dashboard.governance-documents.store'), [
                'title' => 'Board Reviewed Charter',
                'document_type' => 'board_reviewed',
                'category' => 'board',
                'reference_no' => 'BRV-001',
                'version' => 1,
                'status' => 'draft',
                'issued_at' => now()->subDay()->toDateString(),
                'effective_at' => now()->toDateString(),
                'notes' => 'Board review record.',
                'document' => UploadedFile::fake()->create('board-reviewed-charter.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('governance_documents', [
            'title' => 'Board Reviewed Charter',
            'document_type' => 'board_reviewed',
        ]);
    }

    public function test_admin_user_submission_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');

        $this->actingAs($admin)
            ->post(route('dashboard.governance-documents.store'), [
                'title' => 'Board Charter',
                'document_type' => 'resolution',
                'category' => 'board',
                'reference_no' => 'BRD-001',
                'version' => 1,
                'status' => 'draft',
                'issued_at' => now()->subDay()->toDateString(),
                'effective_at' => now()->toDateString(),
                'notes' => 'Initial board charter upload.',
                'document' => UploadedFile::fake()->create('board-charter.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect(route('admin.dashboard'));
    }
}
