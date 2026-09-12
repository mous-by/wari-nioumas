<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffectationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_creating_an_affectation_sets_current_vehicule_and_chauffeur(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $vehicule = Vehicule::factory()->create();
        $chauffeur = Chauffeur::factory()->create();

        $response = $this->actingAs($user)->post('/affectations', [
            'vehicule_id' => $vehicule->id,
            'chauffeur_id' => $chauffeur->id,
            'date_debut' => now()->toDateString(),
            'montant_journalier' => 15000,
            'periodicite' => 'journalier',
        ]);

        $response->assertRedirect(route('affectations.index'));
        $this->assertSame($chauffeur->id, $vehicule->chauffeurActuel()->id);
        $this->assertSame($vehicule->id, $chauffeur->vehiculeActuel()->id);
    }

    public function test_reassigning_a_vehicule_closes_the_previous_affectation(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $vehicule = Vehicule::factory()->create();
        $chauffeurA = Chauffeur::factory()->create();
        $chauffeurB = Chauffeur::factory()->create();

        $premiere = Affectation::factory()->create([
            'vehicule_id' => $vehicule->id,
            'chauffeur_id' => $chauffeurA->id,
            'date_debut' => now()->subMonth(),
            'date_fin' => null,
        ]);

        $this->actingAs($user)->post('/affectations', [
            'vehicule_id' => $vehicule->id,
            'chauffeur_id' => $chauffeurB->id,
            'date_debut' => now()->toDateString(),
            'montant_journalier' => 15000,
            'periodicite' => 'journalier',
        ])->assertRedirect(route('affectations.index'));

        $this->assertNotNull($premiere->fresh()->date_fin);
        $this->assertSame($chauffeurB->id, $vehicule->chauffeurActuel()->id);
        $this->assertNull($chauffeurA->fresh()->vehiculeActuel());
    }

    public function test_reassigning_a_chauffeur_closes_their_previous_affectation(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $chauffeur = Chauffeur::factory()->create();
        $vehiculeA = Vehicule::factory()->create();
        $vehiculeB = Vehicule::factory()->create();

        $premiere = Affectation::factory()->create([
            'vehicule_id' => $vehiculeA->id,
            'chauffeur_id' => $chauffeur->id,
            'date_debut' => now()->subMonth(),
            'date_fin' => null,
        ]);

        $this->actingAs($user)->post('/affectations', [
            'vehicule_id' => $vehiculeB->id,
            'chauffeur_id' => $chauffeur->id,
            'date_debut' => now()->toDateString(),
            'montant_journalier' => 15000,
            'periodicite' => 'journalier',
        ])->assertRedirect(route('affectations.index'));

        $this->assertNotNull($premiere->fresh()->date_fin);
        $this->assertSame($vehiculeB->id, $chauffeur->vehiculeActuel()->id);
    }

    public function test_gestionnaire_can_manually_terminate_an_affectation(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $affectation = Affectation::factory()->create(['date_fin' => null]);

        $this->actingAs($user)->patch("/affectations/{$affectation->id}/terminer")
            ->assertRedirect();

        $this->assertNotNull($affectation->fresh()->date_fin);
    }

    public function test_gestionnaire_can_update_an_affectation(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $affectation = Affectation::factory()->create(['montant_journalier' => 10000]);

        $this->actingAs($user)->put("/affectations/{$affectation->id}", [
            'date_debut' => $affectation->date_debut->toDateString(),
            'montant_journalier' => 18000,
            'periodicite' => 'journalier',
            'observations' => 'Montant corrigé',
        ])->assertRedirect(route('affectations.index'));

        $this->assertEquals(18000, $affectation->fresh()->montant_journalier);
    }

    public function test_permuter_swaps_vehicules_between_two_chauffeurs(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $vehiculeA = Vehicule::factory()->create();
        $vehiculeB = Vehicule::factory()->create();
        $chauffeurX = Chauffeur::factory()->create();
        $chauffeurY = Chauffeur::factory()->create();

        $affX = Affectation::factory()->create([
            'vehicule_id' => $vehiculeA->id, 'chauffeur_id' => $chauffeurX->id,
            'montant_journalier' => 15000, 'periodicite' => 'journalier', 'date_fin' => null,
        ]);
        $affY = Affectation::factory()->create([
            'vehicule_id' => $vehiculeB->id, 'chauffeur_id' => $chauffeurY->id,
            'montant_journalier' => 12000, 'periodicite' => 'journalier', 'date_fin' => null,
        ]);

        $this->actingAs($user)->post('/affectations/permuter', [
            'chauffeur_1_id' => $chauffeurX->id,
            'chauffeur_2_id' => $chauffeurY->id,
            'date_permutation' => now()->toDateString(),
            'periodicite_1' => 'journalier',
            'montant_1' => 15000, // X garde son tarif
            'periodicite_2' => 'journalier',
            'montant_2' => 20000, // Y change de tarif
        ])->assertRedirect(route('affectations.index'));

        $this->assertSame($vehiculeB->id, $chauffeurX->vehiculeActuel()->id);
        $this->assertSame($vehiculeA->id, $chauffeurY->vehiculeActuel()->id);
        $this->assertEquals(15000, Affectation::where('chauffeur_id', $chauffeurX->id)->whereNull('date_fin')->value('montant_journalier'));
        $this->assertEquals(20000, Affectation::where('chauffeur_id', $chauffeurY->id)->whereNull('date_fin')->value('montant_journalier'));

        $this->assertNotNull($affX->fresh()->date_fin);
        $this->assertNotNull($affY->fresh()->date_fin);
        $this->assertStringContainsString($chauffeurY->nom_complet, $affX->fresh()->motif_fin);
        $this->assertStringContainsString($chauffeurX->nom_complet, $affY->fresh()->motif_fin);

        // Chaque véhicule ne doit jamais se retrouver avec deux affectations actives.
        $this->assertSame(1, Affectation::where('vehicule_id', $vehiculeA->id)->whereNull('date_fin')->count());
        $this->assertSame(1, Affectation::where('vehicule_id', $vehiculeB->id)->whereNull('date_fin')->count());
    }

    public function test_permuter_refuses_the_same_chauffeur_twice(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $chauffeur = Chauffeur::factory()->create();
        Affectation::factory()->create(['chauffeur_id' => $chauffeur->id, 'date_fin' => null]);

        $this->actingAs($user)->post('/affectations/permuter', [
            'chauffeur_1_id' => $chauffeur->id,
            'chauffeur_2_id' => $chauffeur->id,
            'date_permutation' => now()->toDateString(),
            'periodicite_1' => 'journalier',
            'montant_1' => 10000,
            'periodicite_2' => 'journalier',
            'montant_2' => 10000,
        ])->assertSessionHasErrors('chauffeur_1_id');
    }

    public function test_permuter_refuses_a_chauffeur_without_active_affectation(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $chauffeurX = Chauffeur::factory()->create();
        $chauffeurY = Chauffeur::factory()->create(); // aucune affectation active

        Affectation::factory()->create(['chauffeur_id' => $chauffeurX->id, 'date_fin' => null]);

        $this->actingAs($user)->post('/affectations/permuter', [
            'chauffeur_1_id' => $chauffeurX->id,
            'chauffeur_2_id' => $chauffeurY->id,
            'date_permutation' => now()->toDateString(),
            'periodicite_1' => 'journalier',
            'montant_1' => 10000,
            'periodicite_2' => 'journalier',
            'montant_2' => 10000,
        ])->assertSessionHasErrors('chauffeur_2_id');
    }

    public function test_user_without_creer_permission_cannot_permuter(): void
    {
        $user = $this->userWithRole('caissier'); // n'a pas affectations.creer
        $chauffeurX = Chauffeur::factory()->create();
        $chauffeurY = Chauffeur::factory()->create();
        Affectation::factory()->create(['chauffeur_id' => $chauffeurX->id, 'date_fin' => null]);
        Affectation::factory()->create(['chauffeur_id' => $chauffeurY->id, 'date_fin' => null]);

        $this->actingAs($user)->post('/affectations/permuter', [
            'chauffeur_1_id' => $chauffeurX->id,
            'chauffeur_2_id' => $chauffeurY->id,
            'date_permutation' => now()->toDateString(),
            'periodicite_1' => 'journalier',
            'montant_1' => 10000,
            'periodicite_2' => 'journalier',
            'montant_2' => 10000,
        ])->assertForbidden();
    }

    public function test_index_page_renders_with_active_affectations(): void
    {
        $user = $this->userWithRole('gestionnaire');
        Affectation::factory()->create(['date_fin' => null]);

        $this->actingAs($user)->get('/affectations')->assertOk();
    }

    public function test_monthly_affectation_accumulates_a_flat_forfait_per_period(): void
    {
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::create(2026, 5, 10));

        $chauffeur = Chauffeur::factory()->create();

        Affectation::factory()->create([
            'chauffeur_id' => $chauffeur->id,
            'montant_journalier' => 150000,
            'periodicite' => 'mensuel',
            'date_debut' => now()->subMonths(3), // 2026-02-10
            'date_fin' => null,
        ]);

        // 3 mois écoulés → 4 forfaits de 150 000, quel que soit le nombre de jours.
        $this->assertEquals(600000, $chauffeur->fresh()->montantDu());

        \Illuminate\Support\Carbon::setTestNow();
    }
}
