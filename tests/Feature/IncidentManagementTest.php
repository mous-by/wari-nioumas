<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentManagementTest extends TestCase
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
            'date_incident' => now()->toDateString(),
            'type' => 'panne',
            'gravite' => 'leger',
            'description' => 'Panne de batterie au dépôt.',
            'cout' => 30000,
            'decision' => 'Batterie remplacée.',
            'statut' => 'ouvert',
        ], $overrides);
    }

    public function test_caissier_cannot_create_an_incident(): void
    {
        // caissier a incidents.voir seulement
        $user = $this->userWithRole('caissier');

        $this->actingAs($user)->post('/incidents', $this->payload())->assertForbidden();
    }

    public function test_responsable_parc_can_create_an_incident(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();

        $response = $this->actingAs($user)->post('/incidents', $this->payload([
            'vehicule_id' => $vehicule->id,
            'type' => 'contravention',
        ]));

        $response->assertRedirect(route('incidents.index'));
        $this->assertDatabaseHas('incidents', [
            'vehicule_id' => $vehicule->id,
            'type' => 'contravention',
            'statut' => 'ouvert',
            'user_id' => $user->id,
        ]);
    }

    public function test_type_must_be_valid(): void
    {
        $user = $this->userWithRole('responsable_parc');

        $response = $this->actingAs($user)->post('/incidents', $this->payload(['type' => 'explosion']));

        $response->assertSessionHasErrors('type');
        $this->assertSame(0, Incident::count());
    }

    public function test_description_is_required(): void
    {
        $user = $this->userWithRole('responsable_parc');

        $response = $this->actingAs($user)->post('/incidents', $this->payload(['description' => '']));

        $response->assertSessionHasErrors('description');
        $this->assertSame(0, Incident::count());
    }

    public function test_responsable_parc_can_resolve_an_incident(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $incident = Incident::factory()->create(['statut' => 'ouvert']);

        $this->actingAs($user)->put("/incidents/{$incident->id}", $this->payload([
            'vehicule_id' => $incident->vehicule_id,
            'type' => $incident->type,
            'decision' => 'Résolu sur place.',
            'statut' => 'resolu',
        ]))->assertRedirect(route('incidents.index'));

        $incident->refresh();
        $this->assertSame('resolu', $incident->statut);
        $this->assertSame('Résolu sur place.', $incident->decision);
    }

    public function test_responsable_parc_cannot_delete_an_incident(): void
    {
        // suppression réservée au directeur_general
        $user = $this->userWithRole('responsable_parc');
        $incident = Incident::factory()->create();

        $this->actingAs($user)->delete("/incidents/{$incident->id}")->assertForbidden();
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
    }

    public function test_directeur_general_can_delete_an_incident(): void
    {
        $user = $this->userWithRole('directeur_general');
        $incident = Incident::factory()->create();

        $this->actingAs($user)->delete("/incidents/{$incident->id}")->assertRedirect();
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    public function test_user_with_voir_can_open_the_detail_page(): void
    {
        $user = $this->userWithRole('caissier'); // incidents.voir seulement
        $incident = Incident::factory()->create(['description' => 'Détail incident visible']);

        $this->actingAs($user)->get("/incidents/{$incident->id}")
            ->assertOk()
            ->assertViewIs('incidents.show')
            ->assertSee('Détail incident visible');
    }
}
