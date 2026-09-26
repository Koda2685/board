<?php

namespace Tests\Feature;

use App\Models\GovernanceDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_non_admin_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Admin Dashboard');
    }

    public function test_non_admin_user_cannot_create_admin_account_from_public_registration(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newadmin@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'is_admin' => true,
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'newadmin@example.com',
            'is_admin' => true,
        ]);
    }

    public function test_admin_user_is_redirected_to_admin_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect('/admin/dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_registered_user_can_access_dashboard_consolidated_report_and_open_document_inline(): void
    {
        $user = User::factory()->create();

        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Urgent Board Minutes',
            'reference_no' => 'MIN-2026-008',
            'document_type' => 'minutes',
            'category' => 'board',
            'file_path' => 'governance-documents/urgent-board-minute.pdf',
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Urgent Board Resolution',
            'reference_no' => 'RES-2026-008',
            'document_type' => 'resolution',
            'category' => 'board',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/urgent-board-resolution.pdf',
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Consolidated Resolution Report')
            ->assertSee('Urgent Board Resolution')
            ->assertSee('RES-2026-008')
            ->assertSee('MIN-2026-008')
            ->assertSee(route('admin.governance-documents.viewer', $resolution))
            ->assertSee(route('admin.governance-documents.viewer', $minute));
    }

    public function test_admin_dashboard_does_not_use_browser_side_reference_generation(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertDontSee('data-auto-reference')
            ->assertDontSee('autoGenerateReference')
            ->assertSee('readonly')
            ->assertSee('admin-document-reference');
    }

    public function test_reference_number_preview_endpoint_generates_next_number_by_document_type(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/dashboard/governance-documents/reference?document_type=policy')
            ->assertOk()
            ->assertJsonPath('reference_no', 'POL-'.now()->format('Y').'-001');
    }

    public function test_admin_dashboard_shows_resolution_register_with_related_minutes_and_supporting_documents(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Board Minutes 2026',
            'reference_no' => 'MIN-2026-MIN',
            'document_type' => 'minutes',
            'category' => 'board',
            'file_path' => 'governance-documents/board-minutes-2026.pdf',
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Board Resolution 2026',
            'reference_no' => 'RES-2026-RES',
            'document_type' => 'resolution',
            'category' => 'board',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/board-resolution-2026.pdf',
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $supportingDocument = GovernanceDocument::factory()->create([
            'title' => 'Board Support Memo',
            'reference_no' => 'COM-2026-SUP',
            'document_type' => 'communication',
            'category' => 'board',
            'related_resolution_id' => $resolution->id,
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/board-support-memo.pdf',
        ]);
        Storage::disk('private')->put($supportingDocument->file_path, '%PDF-1.4\n');

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Resolution Report')
            ->assertSee('Consolidated Resolution Report')
            ->assertSee('Board Resolution 2026')
            ->assertSee('Board Minutes 2026')
            ->assertSee('Board Support Memo')
            ->assertSee('RES-2026-RES')
            ->assertSee('MIN-2026-MIN')
            ->assertSee('COM-2026-SUP')
            ->assertSee(route('admin.governance-documents.viewer', $resolution))
            ->assertSee(route('admin.governance-documents.viewer', $minute))
            ->assertSee(route('admin.governance-documents.viewer', $supportingDocument));
    }

    public function test_consolidated_resolution_report_groups_related_resolution_and_minute_under_one_title(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Annual Board Review Minutes',
            'reference_no' => 'MIN-2026-001',
            'document_type' => 'minutes',
            'category' => 'board',
            'file_path' => 'governance-documents/annual-board-review-minute.pdf',
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Annual Board Review Resolution',
            'reference_no' => 'RES-2026-001',
            'document_type' => 'resolution',
            'category' => 'board',
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/annual-board-review-resolution.pdf',
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $supportingDocument = GovernanceDocument::factory()->create([
            'title' => 'Annual Board Review Memo',
            'reference_no' => 'COM-2026-001',
            'document_type' => 'communication',
            'category' => 'board',
            'related_resolution_id' => $resolution->id,
            'related_minute_id' => $minute->id,
            'file_path' => 'governance-documents/annual-board-review-support.pdf',
        ]);
        Storage::disk('private')->put($supportingDocument->file_path, '%PDF-1.4\n');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Annual Board Review Resolution');
        $response->assertSee('RES-2026-001');
        $response->assertSee('MIN-2026-001');
        $response->assertSee('COM-2026-001');

        $content = $response->getContent();
        $section = explode('Consolidated Resolution Report', $content, 2)[1] ?? $content;
        $section = explode('Record of Resolutions', $section, 2)[0] ?? $section;

        $this->assertLessThan(
            strpos($section, 'MIN-2026-001'),
            strpos($section, 'RES-2026-001')
        );
        $this->assertStringContainsString('Annual Board Review Resolution', $section);
    }

    public function test_consolidated_resolution_report_groups_documents_with_shared_reference_sequence_when_related_ids_are_missing(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Establishment of the initial Board of Directors',
            'reference_no' => 'RES-2026-001',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance-documents/board-establishment-resolution.pdf',
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Minutes of the inaugural Board meeting',
            'reference_no' => 'MIN-2026-001',
            'document_type' => 'minutes',
            'category' => 'board',
            'file_path' => 'governance-documents/board-establishment-minute.pdf',
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $supportingDocument = GovernanceDocument::factory()->create([
            'title' => 'Board establishment supporting memo',
            'reference_no' => 'COM-2026-001',
            'document_type' => 'communication',
            'category' => 'board',
            'file_path' => 'governance-documents/board-establishment-supporting-memo.pdf',
        ]);
        Storage::disk('private')->put($supportingDocument->file_path, '%PDF-1.4\n');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();

        $content = $response->getContent();
        $section = explode('Consolidated Resolution Report', $content, 2)[1] ?? $content;
        $section = explode('Record of Resolutions', $section, 2)[0] ?? $section;

        $this->assertSame(2, preg_match_all('/<tr/', $section));
        $this->assertStringContainsString('RES-2026-001', $section);
        $this->assertStringContainsString('MIN-2026-001', $section);
        $this->assertStringContainsString('COM-2026-001', $section);
    }

    public function test_consolidated_resolution_report_includes_board_reviewed_documents_as_related_records(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Board Review Minutes',
            'reference_no' => 'MIN-2026-017',
            'document_type' => 'minutes',
            'category' => 'board',
            'issued_at' => '2026-05-10',
            'file_path' => 'governance-documents/board-review-minute.pdf',
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Board Review Resolution',
            'reference_no' => 'RES-2026-017',
            'document_type' => 'resolution',
            'category' => 'board',
            'related_minute_id' => $minute->id,
            'issued_at' => '2026-05-10',
            'file_path' => 'governance-documents/board-review-resolution.pdf',
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $reviewedDocument = GovernanceDocument::factory()->create([
            'title' => 'Board Reviewed Summary',
            'reference_no' => 'BRD-2026-017',
            'document_type' => 'board_reviewed',
            'category' => 'board',
            'related_resolution_id' => $resolution->id,
            'related_minute_id' => $minute->id,
            'issued_at' => '2026-05-10',
            'file_path' => 'governance-documents/board-reviewed-summary.pdf',
        ]);
        Storage::disk('private')->put($reviewedDocument->file_path, '%PDF-1.4\n');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Board Review Resolution');
        $response->assertSee('MIN-2026-017');
        $response->assertSee('RES-2026-017');
        $response->assertSee('BRD-2026-017');
        $response->assertSee(route('admin.governance-documents.viewer', $reviewedDocument));
    }

    public function test_consolidated_resolution_report_groups_documents_with_shared_issue_date_and_reference_suffix_even_without_related_ids(): void
    {
        $admin = User::factory()->admin()->create();

        Storage::fake('private');

        $resolution = GovernanceDocument::factory()->create([
            'title' => 'Board Governance Resolution',
            'reference_no' => 'RES/2026/017',
            'document_type' => 'resolution',
            'category' => 'board',
            'issued_at' => '2026-05-10',
            'file_path' => 'governance-documents/board-governance-resolution.pdf',
        ]);
        Storage::disk('private')->put($resolution->file_path, '%PDF-1.4\n');

        $minute = GovernanceDocument::factory()->create([
            'title' => 'Board Governance Minutes',
            'reference_no' => 'MIN/2026/017',
            'document_type' => 'minutes',
            'category' => 'board',
            'issued_at' => '2026-05-10',
            'file_path' => 'governance-documents/board-governance-minute.pdf',
        ]);
        Storage::disk('private')->put($minute->file_path, '%PDF-1.4\n');

        $reviewedDocument = GovernanceDocument::factory()->create([
            'title' => 'Board Governance Reviewed',
            'reference_no' => 'BRD/2026/017',
            'document_type' => 'board_reviewed',
            'category' => 'board',
            'issued_at' => '2026-05-10',
            'file_path' => 'governance-documents/board-governance-reviewed.pdf',
        ]);
        Storage::disk('private')->put($reviewedDocument->file_path, '%PDF-1.4\n');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Board Governance Resolution');
        $response->assertSee('RES/2026/017');
        $response->assertSee('MIN/2026/017');
        $response->assertSee('BRD/2026/017');
    }

    public function test_dashboard_keeps_records_visible_even_when_the_file_is_missing_from_storage(): void
    {
        $admin = User::factory()->admin()->create();

        GovernanceDocument::factory()->create([
            'title' => 'Board Resolution Without Attachment',
            'reference_no' => 'RES-2026-888',
            'document_type' => 'resolution',
            'category' => 'board',
            'status' => 'active',
            'file_path' => 'governance-documents/missing-board-resolution.pdf',
        ]);

        GovernanceDocument::factory()->create([
            'title' => 'Board Minutes Without Attachment',
            'reference_no' => 'MIN-2026-888',
            'document_type' => 'minutes',
            'category' => 'board',
            'status' => 'active',
            'file_path' => 'governance-documents/missing-board-minute.pdf',
        ]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Board Resolution Without Attachment')
            ->assertSee('Board Minutes Without Attachment')
            ->assertDontSee(route('admin.governance-documents.viewer', 1));
    }

    public function test_dashboard_hides_viewer_links_when_the_underlying_file_is_missing_from_storage(): void
    {
        $admin = User::factory()->admin()->create();

        $document = GovernanceDocument::factory()->create([
            'title' => 'Missing Storage File',
            'document_type' => 'resolution',
            'category' => 'board',
            'reference_no' => 'RES/2026/999',
            'file_path' => 'governance-documents/missing-file.pdf',
        ]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertDontSee(route('admin.governance-documents.viewer', $document));
    }

    public function test_document_download_opens_inline_in_browser(): void
    {
        $admin = User::factory()->admin()->create();
        $document = GovernanceDocument::factory()->create([
            'title' => 'Inline Open Document',
            'reference_no' => 'RES-2026-100',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance-documents/inline-open-document.pdf',
        ]);

        Storage::disk('private')->put($document->file_path, '%PDF-1.4\n');

        $this->actingAs($admin)
            ->get(route('admin.governance-documents.download', $document))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename="inline-open-document.pdf"');
    }

    public function test_non_admin_user_can_open_governance_document_inline(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $document = GovernanceDocument::factory()->create([
            'title' => 'Restricted Document',
            'reference_no' => 'RES-2026-101',
            'document_type' => 'resolution',
            'category' => 'board',
            'file_path' => 'governance-documents/restricted-document.pdf',
        ]);

        Storage::disk('private')->put($document->file_path, '%PDF-1.4\n');

        $this->actingAs($user)
            ->get(route('admin.governance-documents.download', $document))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename="restricted-document.pdf"');
    }
}
