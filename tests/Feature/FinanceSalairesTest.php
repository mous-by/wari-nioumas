<?php

namespace Tests\Feature;

use App\Models\Bulletin;
use App\Models\Depense;
use App\Models\Versement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceSalairesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    /**
     * Période analysée : septembre et octobre 2026.
     * - Sept : dépense 50 000, bulletin validé 100 000
     * - Oct  : versement 500 000, bulletin payé 200 000, bulletin brouillon 999 999 (ignoré)
     * - Août : bulletin validé 70 000 (hors période, ignoré)
     */
    private function scenario(): void
    {
        Depense::factory()->create(['montant' => 50000, 'date_depense' => '2026-09-15']);
        Versement::factory()->create(['montant' => 500000, 'date_versement' => '2026-10-10']);

        Bulletin::factory()->create(['salaire_base' => 100000, 'periode_mois' => 9, 'periode_annee' => 2026, 'statut' => 'valide']);
        Bulletin::factory()->create(['salaire_base' => 200000, 'periode_mois' => 10, 'periode_annee' => 2026, 'statut' => 'paye']);
        Bulletin::factory()->create(['salaire_base' => 999999, 'periode_mois' => 10, 'periode_annee' => 2026, 'statut' => 'brouillon']);
        Bulletin::factory()->create(['salaire_base' => 70000, 'periode_mois' => 8, 'periode_annee' => 2026, 'statut' => 'valide']);
    }

    private function rapport(): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->userWithRole('comptable'))
            ->get('/finances?debut=2026-09-01&fin=2026-10-31');
    }

    public function test_salaries_are_added_to_charges_and_result(): void
    {
        $this->scenario();

        $response = $this->rapport()->assertOk();

        $response->assertViewHas('salaires', 300000.0);   // 100 000 + 200 000, brouillon et août exclus
        $response->assertViewHas('charges', 350000.0);    // 50 000 dépenses + 300 000 salaires
        $response->assertViewHas('resultat', 150000.0);   // 500 000 − 350 000
    }

    public function test_monthly_recap_shows_salaries_per_pay_month(): void
    {
        $this->scenario();

        $mensuel = $this->rapport()->viewData('mensuel');

        $this->assertCount(2, $mensuel);

        $sept = $mensuel[0];
        $this->assertSame('2026-09', $sept['mois']->format('Y-m'));
        $this->assertSame(100000.0, $sept['salaires']);
        $this->assertSame(150000.0, $sept['charges']);   // 50 000 + 100 000
        $this->assertSame(-150000.0, $sept['resultat']);

        $oct = $mensuel[1];
        $this->assertSame('2026-10', $oct['mois']->format('Y-m'));
        $this->assertSame(200000.0, $oct['salaires']);
        $this->assertSame(200000.0, $oct['charges']);
        $this->assertSame(300000.0, $oct['resultat']);
    }

    public function test_statistics_are_computed_from_the_period(): void
    {
        $this->scenario();

        $stats = $this->rapport()->viewData('stats');

        $this->assertSame(250000.0, $stats['moyenne_recettes']);  // 500 000 / 2 mois
        $this->assertSame(175000.0, $stats['moyenne_charges']);   // 350 000 / 2 mois
        $this->assertEqualsWithDelta(30.0, $stats['taux_marge'], 0.001);    // 150 000 / 500 000
        $this->assertEqualsWithDelta(85.714, $stats['part_salaires'], 0.001); // 300 000 / 350 000
        $this->assertSame(2, $stats['nb_salaries']);
        $this->assertSame('2026-10', $stats['mois_rentable']['mois']->format('Y-m'));
        $this->assertSame('2026-10', $stats['mois_charge']['mois']->format('Y-m'));
    }

    public function test_a_month_without_bulletin_has_zero_salaries(): void
    {
        Versement::factory()->create(['montant' => 100000, 'date_versement' => '2026-10-02']);

        $response = $this->actingAs($this->userWithRole('comptable'))
            ->get('/finances?debut=2026-10-01&fin=2026-10-31');

        $response->assertViewHas('salaires', 0.0);
        // Sans charges, la part des salaires n'a pas de sens : null (affiché « — »).
        $response->assertViewHas('stats', fn ($stats) => $stats['part_salaires'] === null && $stats['nb_salaries'] === 0);
    }

    public function test_draft_bulletins_are_never_counted(): void
    {
        Bulletin::factory()->create(['salaire_base' => 500000, 'periode_mois' => 10, 'periode_annee' => 2026, 'statut' => 'brouillon']);

        $this->rapport()->assertViewHas('salaires', 0.0);
    }

    public function test_csv_export_contains_the_salary_lines(): void
    {
        $this->scenario();

        $response = $this->actingAs($this->userWithRole('comptable'))
            ->get('/finances/export/csv?debut=2026-09-01&fin=2026-10-31');

        $contenu = $response->streamedContent();
        $this->assertStringContainsString('Mois;Recettes;Salaires;Charges;Résultat', $contenu);
        // fputcsv met entre guillemets tout champ contenant un espace (libellés et montants).
        $this->assertStringContainsString('"TOTAL salaires";"300 000"', $contenu);
        $this->assertStringContainsString('"RÉSULTAT NET";"150 000"', $contenu);
    }

    public function test_pdf_export_renders_with_salaries(): void
    {
        $this->scenario();

        $response = $this->actingAs($this->userWithRole('comptable'))
            ->get('/finances/export/pdf?debut=2026-09-01&fin=2026-10-31');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
