<?php

namespace Tests\Feature;

use App\Models\Accident;
use App\Models\Depense;
use App\Models\Versement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_responsable_parc_cannot_view_finances(): void
    {
        // responsable_parc n'a pas finances.voir
        $user = $this->userWithRole('responsable_parc');

        $this->actingAs($user)->get('/finances')->assertForbidden();
    }

    public function test_comptable_can_view_finances(): void
    {
        $user = $this->userWithRole('comptable');

        $this->actingAs($user)->get('/finances')->assertOk()->assertViewIs('finances.index');
    }

    public function test_report_computes_resultat_as_recettes_minus_charges(): void
    {
        $user = $this->userWithRole('comptable');

        Versement::factory()->create(['montant' => 200000, 'date_versement' => now()->toDateString()]);
        Depense::factory()->create(['montant' => 50000, 'date_depense' => now()->toDateString()]);
        Accident::factory()->create(['cout_reparation' => 30000, 'date_accident' => now()->toDateString()]);

        $response = $this->actingAs($user)->get('/finances');

        $response->assertOk();
        $response->assertViewHas('recettes', 200000.0);
        $response->assertViewHas('charges', 80000.0);   // 50 000 dépenses + 30 000 accident
        $response->assertViewHas('resultat', 120000.0); // 200 000 − 80 000
    }
}
