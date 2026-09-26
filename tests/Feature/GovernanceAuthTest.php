<?php

namespace Tests\Feature;

use Tests\TestCase;

class GovernanceAuthTest extends TestCase
{
    public function test_guest_is_redirected_from_governance_document_routes(): void
    {
        $this->get('/governance-documents')->assertRedirect('/login');
        $this->post('/governance-documents', [])->assertRedirect('/login');
    }

    public function test_guest_is_redirected_from_nested_governance_record_routes(): void
    {
        $this->get('/governance-documents/1/governance-records')->assertRedirect('/login');
        $this->post('/governance-documents/1/governance-records', [])->assertRedirect('/login');
    }
}
