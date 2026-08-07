<?php

namespace Tests\Feature;

use App\Models\Chauffeur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChauffeurManagementTest extends TestCase
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
            'matricule' => 'CH-001',
            'nom' => 'Traore',
            'prenom' => 'Ibrahim',
            'telephone' => '70112233',
            'adresse' => 'Bamako',
            'nina' => 'NINA12345',
            'permis_numero' => 'PC-99887',
            'permis_date_validite' => now()->addYear()->toDateString(),
            'date_embauche' => now()->toDateString(),
            'statut' => 'actif',
            'observations' => null,
        ], $overrides);
    }

    public function test_user_without_permission_cannot_view_chauffeurs(): void
    {
        $user = $this->userWithRole('comptable');

        $this->actingAs($user)->get('/chauffeurs')->assertOk();
        $this->actingAs($user)->post('/chauffeurs', $this->validPayload())->assertForbidden();
    }

    public function test_gestionnaire_can_create_a_chauffeur(): void
    {
        $user = $this->userWithRole('gestionnaire');

        $response = $this->actingAs($user)->post('/chauffeurs', $this->validPayload());

        $response->assertRedirect(route('chauffeurs.index'));
        $this->assertDatabaseHas('chauffeurs', ['nina' => 'NINA12345']);

        $chauffeur = Chauffeur::firstWhere('nina', 'NINA12345');
        $this->assertNotEmpty($chauffeur->matricule);
        $this->assertSame(1, $chauffeur->statutHistoriques()->count());
    }

    public function test_invalid_malian_phone_is_rejected(): void
    {
        $user = $this->userWithRole('gestionnaire');

        $response = $this->actingAs($user)->post('/chauffeurs', $this->validPayload(['telephone' => '123']));

        $response->assertSessionHasErrors('telephone');
        $this->assertDatabaseMissing('chauffeurs', ['nina' => 'NINA12345']);
    }

    public function test_updating_statut_logs_history(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $chauffeur = Chauffeur::factory()->create(['statut' => 'actif']);

        $payload = $this->validPayload([
            'matricule' => $chauffeur->matricule,
            'nina' => $chauffeur->nina,
            'telephone' => $chauffeur->telephone,
            'statut' => 'suspendu',
        ]);

        $this->actingAs($user)->put("/chauffeurs/{$chauffeur->id}", $payload)
            ->assertRedirect(route('chauffeurs.index'));

        $this->assertSame('suspendu', $chauffeur->fresh()->statut);
        $this->assertSame(1, $chauffeur->statutHistoriques()->count());
        $this->assertSame('suspendu', $chauffeur->statutHistoriques()->first()->nouveau_statut);
    }

    public function test_responsable_parc_can_soft_delete_a_chauffeur(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $chauffeur = Chauffeur::factory()->create();

        $this->actingAs($user)->delete("/chauffeurs/{$chauffeur->id}")->assertRedirect();

        $this->assertSoftDeleted('chauffeurs', ['id' => $chauffeur->id]);
    }

    public function test_gestionnaire_cannot_delete_a_chauffeur(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $chauffeur = Chauffeur::factory()->create();

        $this->actingAs($user)->delete("/chauffeurs/{$chauffeur->id}")->assertForbidden();

        $this->assertNotSoftDeleted('chauffeurs', ['id' => $chauffeur->id]);
    }
}
