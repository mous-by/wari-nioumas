<?php

namespace Tests\Feature;

use App\Models\Bulletin;
use App\Models\MandatPaiement;
use App\Models\Personnel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MandatManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    private function bulletinValide(int $mois, int $annee, float $net = 150000): Bulletin
    {
        $personnel = Personnel::factory()->create(['salaire_base' => $net]);

        return Bulletin::factory()->create([
            'personnel_id' => $personnel->id,
            'periode_mois' => $mois,
            'periode_annee' => $annee,
            'salaire_base' => $net,
            'statut' => 'valide',
        ]);
    }

    public function test_creating_a_mandat_pulls_validated_bulletins(): void
    {
        $user = $this->userWithRole('comptable');
        $this->bulletinValide(6, 2026, 150000);
        $this->bulletinValide(6, 2026, 120000);
        // un bulletin en brouillon ne doit pas être inclus
        Bulletin::factory()->create(['periode_mois' => 6, 'periode_annee' => 2026, 'salaire_base' => 90000, 'statut' => 'brouillon']);

        $response = $this->actingAs($user)->post('/mandats', [
            'periode_mois' => 6, 'periode_annee' => 2026, 'date_mandat' => '2026-06-30',
        ]);

        $mandat = MandatPaiement::first();
        $response->assertRedirect(route('mandats.show', $mandat));
        $this->assertSame(2, $mandat->lignes()->count());
        $this->assertEqualsWithDelta(270000, (float) $mandat->montant_total, 0.01);
        $this->assertStringStartsWith('MP-', $mandat->numero);
    }

    public function test_mandat_creation_fails_without_validated_bulletins(): void
    {
        $user = $this->userWithRole('comptable');
        Bulletin::factory()->create(['periode_mois' => 6, 'periode_annee' => 2026, 'statut' => 'brouillon']);

        $this->actingAs($user)->post('/mandats', [
            'periode_mois' => 6, 'periode_annee' => 2026, 'date_mandat' => '2026-06-30',
        ])->assertSessionHasErrors('periode');

        $this->assertSame(0, MandatPaiement::count());
    }

    public function test_only_directeur_general_can_sign_a_mandat(): void
    {
        $comptable = $this->userWithRole('comptable');
        $dg = $this->userWithRole('directeur_general');
        $mandat = MandatPaiement::factory()->create(['statut' => 'brouillon']);

        // comptable n'a pas mandats.signer
        $this->actingAs($comptable)->patch("/mandats/{$mandat->id}/signer")->assertForbidden();

        // DG peut signer
        $this->actingAs($dg)->patch("/mandats/{$mandat->id}/signer")->assertRedirect();
        $mandat->refresh();
        $this->assertSame('signe', $mandat->statut);
        $this->assertSame($dg->id, $mandat->signataire_id);
        $this->assertNotNull($mandat->date_signature);
    }

    public function test_status_advances_and_marks_bulletins_paid(): void
    {
        $user = $this->userWithRole('directeur_general');
        $bulletin = $this->bulletinValide(6, 2026, 150000);
        $mandat = MandatPaiement::factory()->create(['statut' => 'signe']);
        $mandat->lignes()->create(['personnel_id' => $bulletin->personnel_id, 'bulletin_id' => $bulletin->id, 'montant' => 150000]);

        // signe -> depose
        $this->actingAs($user)->patch("/mandats/{$mandat->id}/statut")->assertRedirect();
        $this->assertSame('depose', $mandat->fresh()->statut);

        // depose -> paye (marque les bulletins liés comme payés)
        $this->actingAs($user)->patch("/mandats/{$mandat->id}/statut")->assertRedirect();
        $this->assertSame('paye', $mandat->fresh()->statut);
        $this->assertSame('paye', $bulletin->fresh()->statut);
    }

    public function test_mandat_pdf_is_generated(): void
    {
        $user = $this->userWithRole('comptable');
        $mandat = MandatPaiement::factory()->create();

        $response = $this->actingAs($user)->get("/mandats/{$mandat->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
