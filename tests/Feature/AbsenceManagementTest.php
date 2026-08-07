<?php

namespace Tests\Feature;

use App\Models\Absence;
use App\Models\Chauffeur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceManagementTest extends TestCase
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
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->toDateString(),
            'motif' => 'Maladie',
        ], $overrides);
    }

    public function test_comptable_cannot_create_an_absence(): void
    {
        // comptable a absences.voir seulement
        $user = $this->userWithRole('comptable');

        $this->actingAs($user)->post('/absences', $this->payload())->assertForbidden();
    }

    public function test_gestionnaire_can_create_an_absence(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $chauffeur = Chauffeur::factory()->create();

        $response = $this->actingAs($user)->post('/absences', $this->payload([
            'chauffeur_id' => $chauffeur->id,
            'motif' => 'Congé',
        ]));

        $response->assertRedirect(route('absences.index'));
        $this->assertDatabaseHas('absences', [
            'chauffeur_id' => $chauffeur->id,
            'motif' => 'Congé',
            'statut' => 'en_attente',
            'user_id' => $user->id,
        ]);
    }

    public function test_date_fin_must_not_precede_date_debut(): void
    {
        $user = $this->userWithRole('gestionnaire');

        $response = $this->actingAs($user)->post('/absences', $this->payload([
            'date_debut' => now()->toDateString(),
            'date_fin' => now()->subDay()->toDateString(),
        ]));

        $response->assertSessionHasErrors('date_fin');
        $this->assertSame(0, Absence::count());
    }

    public function test_directeur_general_can_accept_an_absence(): void
    {
        // la validation des absences est réservée au DG
        $user = $this->userWithRole('directeur_general');
        $absence = Absence::factory()->create(['statut' => 'en_attente']);

        $this->actingAs($user)->patch("/absences/{$absence->id}/accepter")->assertRedirect();

        $this->assertDatabaseHas('absences', [
            'id' => $absence->id,
            'statut' => 'acceptee',
            'validee_par' => $user->id,
        ]);
    }

    public function test_directeur_general_can_refuse_an_absence(): void
    {
        $user = $this->userWithRole('directeur_general');
        $absence = Absence::factory()->create(['statut' => 'en_attente']);

        $this->actingAs($user)->patch("/absences/{$absence->id}/refuser")->assertRedirect();

        $this->assertDatabaseHas('absences', [
            'id' => $absence->id,
            'statut' => 'refusee',
            'validee_par' => $user->id,
        ]);
    }

    public function test_gestionnaire_cannot_accept_an_absence(): void
    {
        // désormais SEUL le DG peut valider une absence
        $user = $this->userWithRole('gestionnaire');
        $absence = Absence::factory()->create(['statut' => 'en_attente']);

        $this->actingAs($user)->patch("/absences/{$absence->id}/accepter")->assertForbidden();
        $this->assertDatabaseHas('absences', ['id' => $absence->id, 'statut' => 'en_attente']);
    }

    public function test_gestionnaire_cannot_delete_an_absence(): void
    {
        // la suppression est réservée au directeur_general
        $user = $this->userWithRole('gestionnaire');
        $absence = Absence::factory()->create();

        $this->actingAs($user)->delete("/absences/{$absence->id}")->assertForbidden();
        $this->assertDatabaseHas('absences', ['id' => $absence->id]);
    }

    public function test_directeur_general_can_delete_an_absence(): void
    {
        $user = $this->userWithRole('directeur_general');
        $absence = Absence::factory()->create();

        $this->actingAs($user)->delete("/absences/{$absence->id}")->assertRedirect();
        $this->assertDatabaseMissing('absences', ['id' => $absence->id]);
    }
}
