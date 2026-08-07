<?php

namespace Tests\Feature;

use App\Models\Versement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_comptable_can_export_finance_pdf(): void
    {
        $user = $this->userWithRole('comptable');
        Versement::factory()->create(['montant' => 100000, 'date_versement' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/finances/export/pdf');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_comptable_can_export_finance_csv(): void
    {
        $user = $this->userWithRole('comptable');

        $response = $this->actingAs($user)->get('/finances/export/csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('RÉSULTAT NET', $content);
    }

    public function test_responsable_parc_cannot_export_finances(): void
    {
        $user = $this->userWithRole('responsable_parc');

        $this->actingAs($user)->get('/finances/export/pdf')->assertForbidden();
        $this->actingAs($user)->get('/finances/export/csv')->assertForbidden();
    }
}
