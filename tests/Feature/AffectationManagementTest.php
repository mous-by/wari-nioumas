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
            'observations' => 'Montant corrigé',
        ])->assertRedirect(route('affectations.index'));

        $this->assertEquals(18000, $affectation->fresh()->montant_journalier);
    }
}
