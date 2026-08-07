<?php

namespace Tests\Feature;

use App\Models\Absence;
use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Versement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VersementManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'chauffeur_id' => Chauffeur::factory()->create()->id,
            'date_versement' => now()->toDateString(),
            'montant' => 12000,
            'observations' => null,
        ], $overrides);
    }

    private function chauffeurAvecAffectation(float $montantJournalier = 10000, int $joursEcoules = 9): Chauffeur
    {
        $chauffeur = Chauffeur::factory()->create();

        Affectation::factory()->create([
            'chauffeur_id' => $chauffeur->id,
            'montant_journalier' => $montantJournalier,
            'date_debut' => Carbon::today()->subDays($joursEcoules), // bornes incluses
            'date_fin' => null,
        ]);

        return $chauffeur;
    }

    // --- Autorisations & CRUD -------------------------------------------------

    public function test_responsable_parc_cannot_create_a_versement(): void
    {
        // responsable_parc a recettes.voir seulement, pas recettes.creer
        $user = $this->userWithRole('responsable_parc');

        $this->actingAs($user)->post('/recettes', $this->payload())->assertForbidden();
    }

    public function test_comptable_can_create_a_versement(): void
    {
        $user = $this->userWithRole('comptable');
        $chauffeur = Chauffeur::factory()->create();

        $response = $this->actingAs($user)->post('/recettes', $this->payload([
            'chauffeur_id' => $chauffeur->id,
            'montant' => 20000,
        ]));

        $response->assertRedirect(route('recettes.index'));
        $this->assertDatabaseHas('versements', [
            'chauffeur_id' => $chauffeur->id,
            'montant' => 20000,
            'user_id' => $user->id,
        ]);
    }

    public function test_versement_requires_a_positive_amount(): void
    {
        $user = $this->userWithRole('comptable');

        $response = $this->actingAs($user)->post('/recettes', $this->payload(['montant' => 0]));

        $response->assertSessionHasErrors('montant');
        $this->assertSame(0, Versement::count());
    }

    public function test_comptable_can_update_a_versement(): void
    {
        $user = $this->userWithRole('comptable');
        $versement = Versement::factory()->create(['montant' => 5000]);

        $this->actingAs($user)->put("/recettes/{$versement->id}", [
            'date_versement' => $versement->date_versement->toDateString(),
            'montant' => 15000,
        ])->assertRedirect(route('recettes.index'));

        $this->assertEquals(15000, $versement->fresh()->montant);
    }

    public function test_comptable_cannot_delete_a_versement(): void
    {
        // la suppression est réservée au directeur_general
        $user = $this->userWithRole('comptable');
        $versement = Versement::factory()->create();

        $this->actingAs($user)->delete("/recettes/{$versement->id}")->assertForbidden();
        $this->assertDatabaseHas('versements', ['id' => $versement->id]);
    }

    public function test_directeur_general_can_delete_a_versement(): void
    {
        $user = $this->userWithRole('directeur_general');
        $versement = Versement::factory()->create();

        $this->actingAs($user)->delete("/recettes/{$versement->id}")->assertRedirect();
        $this->assertDatabaseMissing('versements', ['id' => $versement->id]);
    }

    // --- Compte à rebours (accumulation automatique) --------------------------

    public function test_montant_du_accumulates_daily_from_affectation(): void
    {
        $chauffeur = $this->chauffeurAvecAffectation(10000, 9); // 10 jours inclus

        $this->assertEqualsWithDelta(100000, $chauffeur->fresh()->montantDu(), 0.01);
    }

    public function test_accepted_absence_reduces_montant_du(): void
    {
        $chauffeur = $this->chauffeurAvecAffectation(10000, 9); // 10 jours → 100 000

        Absence::factory()->create([
            'chauffeur_id' => $chauffeur->id,
            'date_debut' => Carbon::today()->subDays(5),
            'date_fin' => Carbon::today()->subDays(4), // 2 jours
            'statut' => 'acceptee',
        ]);

        // 100 000 − 2 × 10 000 = 80 000
        $this->assertEqualsWithDelta(80000, $chauffeur->fresh()->montantDu(), 0.01);
    }

    public function test_pending_absence_does_not_reduce_montant_du(): void
    {
        $chauffeur = $this->chauffeurAvecAffectation(10000, 9);

        Absence::factory()->create([
            'chauffeur_id' => $chauffeur->id,
            'date_debut' => Carbon::today()->subDays(5),
            'date_fin' => Carbon::today()->subDays(4),
            'statut' => 'en_attente',
        ]);

        $this->assertEqualsWithDelta(100000, $chauffeur->fresh()->montantDu(), 0.01);
    }

    public function test_solde_is_montant_du_minus_total_verse(): void
    {
        $chauffeur = $this->chauffeurAvecAffectation(10000, 9); // dû = 100 000

        Versement::factory()->create(['chauffeur_id' => $chauffeur->id, 'montant' => 30000]);

        $this->assertEqualsWithDelta(70000, $chauffeur->fresh()->solde(), 0.01);
    }
}
