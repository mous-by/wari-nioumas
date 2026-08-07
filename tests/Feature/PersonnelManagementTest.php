<?php

namespace Tests\Feature;

use App\Models\Personnel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelManagementTest extends TestCase
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
            'nom' => 'KONE',
            'prenom' => 'Awa',
            'poste' => 'Comptable',
            'salaire_base' => 150000,
            'statut' => 'actif',
        ], $overrides);
    }

    public function test_caissier_cannot_create_personnel(): void
    {
        // caissier n'a pas personnel.creer (voir seulement)
        $user = $this->userWithRole('caissier');

        $this->actingAs($user)->post('/personnel', $this->payload())->assertForbidden();
    }

    public function test_gestionnaire_can_create_personnel_and_logs_initial_salary(): void
    {
        $user = $this->userWithRole('gestionnaire');

        $response = $this->actingAs($user)->post('/personnel', $this->payload(['salaire_base' => 200000]));

        $response->assertRedirect(route('personnel.index'));
        $this->assertDatabaseHas('personnels', ['nom' => 'KONE', 'salaire_base' => 200000]);

        $personnel = Personnel::first();
        $this->assertDatabaseHas('personnel_salaire_historiques', [
            'personnel_id' => $personnel->id,
            'ancien_salaire' => null,
            'nouveau_salaire' => 200000,
        ]);
    }

    public function test_matricule_is_auto_generated(): void
    {
        $user = $this->userWithRole('gestionnaire');

        $this->actingAs($user)->post('/personnel', $this->payload());

        $this->assertStringStartsWith('EMP-', Personnel::first()->matricule);
    }

    public function test_changing_salary_logs_history(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $personnel = Personnel::factory()->create(['salaire_base' => 100000]);

        $this->actingAs($user)->put("/personnel/{$personnel->id}", $this->payload([
            'nom' => $personnel->nom, 'prenom' => $personnel->prenom, 'poste' => $personnel->poste,
            'salaire_base' => 175000,
        ]))->assertRedirect(route('personnel.index'));

        $this->assertEquals(175000, $personnel->fresh()->salaire_base);
        $this->assertDatabaseHas('personnel_salaire_historiques', [
            'personnel_id' => $personnel->id,
            'ancien_salaire' => 100000,
            'nouveau_salaire' => 175000,
        ]);
    }

    public function test_updating_without_salary_change_does_not_log_history(): void
    {
        $user = $this->userWithRole('gestionnaire');
        $personnel = Personnel::factory()->create(['salaire_base' => 120000, 'poste' => 'Caissier']);

        $this->actingAs($user)->put("/personnel/{$personnel->id}", $this->payload([
            'nom' => $personnel->nom, 'prenom' => $personnel->prenom,
            'poste' => 'Chef caissier', 'salaire_base' => 120000,
        ]));

        // Aucune ligne d'historique (le salaire n'a pas changé)
        $this->assertSame(0, $personnel->salaireHistoriques()->count());
    }

    public function test_gestionnaire_cannot_delete_personnel(): void
    {
        // suppression réservée au directeur_general
        $user = $this->userWithRole('gestionnaire');
        $personnel = Personnel::factory()->create();

        $this->actingAs($user)->delete("/personnel/{$personnel->id}")->assertForbidden();
    }

    public function test_directeur_general_can_delete_personnel(): void
    {
        $user = $this->userWithRole('directeur_general');
        $personnel = Personnel::factory()->create();

        $this->actingAs($user)->delete("/personnel/{$personnel->id}")->assertRedirect();
        $this->assertSoftDeleted('personnels', ['id' => $personnel->id]);
    }
}
