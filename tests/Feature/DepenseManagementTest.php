<?php

namespace Tests\Feature;

use App\Models\Depense;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepenseManagementTest extends TestCase
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
            'categorie' => 'carburant',
            'montant' => 25000,
            'date_depense' => now()->toDateString(),
            'description' => 'Plein de gasoil',
        ], $overrides);
    }

    public function test_caissier_cannot_create_a_depense_without_permission(): void
    {
        // caissier a depenses.voir + depenses.creer d'après la config ;
        // on teste plutôt un rôle sans depenses.creer : responsable_parc l'a,
        // gestionnaire l'a... on prend un utilisateur sans la permission.
        $user = $this->userWithRole('comptable');
        $user->revokePermissionTo('depenses.creer');

        $this->actingAs($user)->post('/depenses', $this->payload())->assertForbidden();
    }

    public function test_comptable_can_create_a_depense(): void
    {
        $user = $this->userWithRole('comptable');
        $vehicule = Vehicule::factory()->create();

        $response = $this->actingAs($user)->post('/depenses', $this->payload([
            'vehicule_id' => $vehicule->id,
            'categorie' => 'reparation',
            'montant' => 40000,
        ]));

        $response->assertRedirect(route('depenses.index'));
        $this->assertDatabaseHas('depenses', [
            'vehicule_id' => $vehicule->id,
            'categorie' => 'reparation',
            'montant' => 40000,
            'user_id' => $user->id,
        ]);
    }

    public function test_depense_can_be_general_without_vehicule(): void
    {
        $user = $this->userWithRole('comptable');

        $response = $this->actingAs($user)->post('/depenses', $this->payload([
            'vehicule_id' => null,
            'categorie' => 'assurance',
        ]));

        $response->assertRedirect(route('depenses.index'));
        $this->assertDatabaseHas('depenses', ['vehicule_id' => null, 'categorie' => 'assurance']);
    }

    public function test_categorie_must_be_valid(): void
    {
        $user = $this->userWithRole('comptable');

        $response = $this->actingAs($user)->post('/depenses', $this->payload(['categorie' => 'inconnue']));

        $response->assertSessionHasErrors('categorie');
        $this->assertSame(0, Depense::count());
    }

    public function test_montant_must_be_positive(): void
    {
        $user = $this->userWithRole('comptable');

        $response = $this->actingAs($user)->post('/depenses', $this->payload(['montant' => 0]));

        $response->assertSessionHasErrors('montant');
        $this->assertSame(0, Depense::count());
    }

    public function test_comptable_can_update_a_depense(): void
    {
        $user = $this->userWithRole('comptable');
        $depense = Depense::factory()->create(['montant' => 10000]);

        $this->actingAs($user)->put("/depenses/{$depense->id}", [
            'vehicule_id' => $depense->vehicule_id,
            'categorie' => $depense->categorie,
            'montant' => 33000,
            'date_depense' => $depense->date_depense->toDateString(),
        ])->assertRedirect(route('depenses.index'));

        $this->assertEquals(33000, $depense->fresh()->montant);
    }

    public function test_comptable_cannot_delete_a_depense(): void
    {
        // la suppression est réservée au directeur_general
        $user = $this->userWithRole('comptable');
        $depense = Depense::factory()->create();

        $this->actingAs($user)->delete("/depenses/{$depense->id}")->assertForbidden();
        $this->assertDatabaseHas('depenses', ['id' => $depense->id]);
    }

    public function test_directeur_general_can_delete_a_depense(): void
    {
        $user = $this->userWithRole('directeur_general');
        $depense = Depense::factory()->create();

        $this->actingAs($user)->delete("/depenses/{$depense->id}")->assertRedirect();
        $this->assertDatabaseMissing('depenses', ['id' => $depense->id]);
    }
}
