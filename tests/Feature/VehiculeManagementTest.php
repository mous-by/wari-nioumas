<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiculeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'immatriculation' => 'AB-1234-CD',
            'marque' => 'Toyota',
            'modele' => 'Hiace',
            'type' => 'Minibus',
            'annee' => 2020,
            'etat' => 'actif',
            'observations' => null,
        ], $overrides);
    }

    public function test_user_without_permission_cannot_create_vehicule(): void
    {
        $user = $this->userWithRole('comptable');

        $this->actingAs($user)->post('/vehicules', $this->validPayload())->assertForbidden();
    }

    public function test_gestionnaire_can_create_a_vehicule(): void
    {
        $user = $this->userWithRole('gestionnaire');

        $response = $this->actingAs($user)->post('/vehicules', $this->validPayload());

        $response->assertRedirect(route('vehicules.index'));
        $this->assertDatabaseHas('vehicules', ['immatriculation' => 'AB-1234-CD']);

        $vehicule = Vehicule::firstWhere('immatriculation', 'AB-1234-CD');
        $this->assertSame(1, $vehicule->etatHistoriques()->count());
    }

    public function test_updating_etat_logs_history(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $vehicule = Vehicule::factory()->create(['etat' => 'actif']);

        $payload = $this->validPayload([
            'immatriculation' => $vehicule->immatriculation,
            'etat' => 'garage',
        ]);

        $this->actingAs($user)->put("/vehicules/{$vehicule->id}", $payload)
            ->assertRedirect(route('vehicules.index'));

        $this->assertSame('garage', $vehicule->fresh()->etat);
        $this->assertSame(1, $vehicule->etatHistoriques()->count());
    }

    public function test_responsable_parc_can_soft_delete_a_vehicule(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();

        $this->actingAs($user)->delete("/vehicules/{$vehicule->id}")->assertRedirect();

        $this->assertSoftDeleted('vehicules', ['id' => $vehicule->id]);
    }

    public function test_gestionnaire_cannot_delete_a_vehicule(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $vehicule = Vehicule::factory()->create();

        $this->actingAs($user)->delete("/vehicules/{$vehicule->id}")->assertForbidden();

        $this->assertNotSoftDeleted('vehicules', ['id' => $vehicule->id]);
    }

    public function test_deleting_a_vehicule_removes_its_affectations_and_page_still_loads(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();
        $chauffeur = Chauffeur::factory()->create();
        $affectation = Affectation::factory()->create([
            'vehicule_id' => $vehicule->id, 'chauffeur_id' => $chauffeur->id,
        ]);

        $this->actingAs($user)->delete("/vehicules/{$vehicule->id}")->assertRedirect();

        // l'affectation part avec le véhicule (plus de référence orpheline)
        $this->assertDatabaseMissing('affectations', ['id' => $affectation->id]);
        // et la page Affectations ne plante plus
        $this->actingAs($user)->get('/affectations')->assertOk();
    }
}
