<?php

namespace Tests\Feature;

use App\Models\Accident;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccidentManagementTest extends TestCase
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
            'vehicule_id' => Vehicule::factory()->create()->id,
            'chauffeur_id' => null,
            'date_accident' => now()->toDateString(),
            'lieu' => 'Bamako',
            'gravite' => 'moyen',
            'responsabilite' => 'chauffeur',
            'description' => 'Collision légère au carrefour.',
            'cout_reparation' => 150000,
            'decision' => 'Réparation en atelier.',
            'statut' => 'en_cours',
        ], $overrides);
    }

    public function test_caissier_cannot_create_an_accident(): void
    {
        // caissier a accidents.voir seulement
        $user = $this->userWithRole('caissier');

        $this->actingAs($user)->post('/accidents', $this->payload())->assertForbidden();
    }

    public function test_responsable_parc_can_create_an_accident(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();

        $response = $this->actingAs($user)->post('/accidents', $this->payload([
            'vehicule_id' => $vehicule->id,
            'gravite' => 'grave',
        ]));

        $response->assertRedirect(route('accidents.index'));
        $this->assertDatabaseHas('accidents', [
            'vehicule_id' => $vehicule->id,
            'gravite' => 'grave',
            'statut' => 'en_cours',
            'user_id' => $user->id,
        ]);
    }

    public function test_accident_can_be_recorded_without_vehicule_or_chauffeur(): void
    {
        $user = $this->userWithRole('responsable_parc');

        $response = $this->actingAs($user)->post('/accidents', $this->payload([
            'vehicule_id' => null,
            'chauffeur_id' => null,
        ]));

        $response->assertRedirect(route('accidents.index'));
        $this->assertDatabaseHas('accidents', ['vehicule_id' => null, 'chauffeur_id' => null]);
    }

    public function test_gravite_must_be_valid(): void
    {
        $user = $this->userWithRole('responsable_parc');

        $response = $this->actingAs($user)->post('/accidents', $this->payload(['gravite' => 'enorme']));

        $response->assertSessionHasErrors('gravite');
        $this->assertSame(0, Accident::count());
    }

    public function test_description_is_required(): void
    {
        $user = $this->userWithRole('responsable_parc');

        $response = $this->actingAs($user)->post('/accidents', $this->payload(['description' => '']));

        $response->assertSessionHasErrors('description');
        $this->assertSame(0, Accident::count());
    }

    public function test_responsable_parc_can_update_an_accident_decision_and_close_it(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $accident = Accident::factory()->create(['statut' => 'en_cours']);

        $this->actingAs($user)->put("/accidents/{$accident->id}", $this->payload([
            'vehicule_id' => $accident->vehicule_id,
            'decision' => 'Dossier clôturé, indemnisation reçue.',
            'statut' => 'clos',
        ]))->assertRedirect(route('accidents.index'));

        $accident->refresh();
        $this->assertSame('clos', $accident->statut);
        $this->assertSame('Dossier clôturé, indemnisation reçue.', $accident->decision);
    }

    public function test_responsable_parc_cannot_delete_an_accident(): void
    {
        // suppression réservée au directeur_general
        $user = $this->userWithRole('responsable_parc');
        $accident = Accident::factory()->create();

        $this->actingAs($user)->delete("/accidents/{$accident->id}")->assertForbidden();
        $this->assertDatabaseHas('accidents', ['id' => $accident->id]);
    }

    public function test_directeur_general_can_delete_an_accident(): void
    {
        $user = $this->userWithRole('directeur_general');
        $accident = Accident::factory()->create();

        $this->actingAs($user)->delete("/accidents/{$accident->id}")->assertRedirect();
        $this->assertDatabaseMissing('accidents', ['id' => $accident->id]);
    }

    public function test_user_with_voir_can_open_the_detail_page(): void
    {
        $user = $this->userWithRole('caissier'); // accidents.voir seulement
        $accident = Accident::factory()->create(['description' => 'Détail visible ici']);

        $this->actingAs($user)->get("/accidents/{$accident->id}")
            ->assertOk()
            ->assertViewIs('accidents.show')
            ->assertSee('Détail visible ici');
    }
}
