<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\MouvementCaisse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaisseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_gestionnaire_cannot_open_a_caisse(): void
    {
        // gestionnaire a caisse.voir seulement, pas caisse.ouvrir
        $user = $this->userWithRole('gestionnaire');

        $this->actingAs($user)->post('/caisse/ouvrir', ['solde_ouverture' => 50000])->assertForbidden();
    }

    public function test_caissier_can_open_a_caisse(): void
    {
        $user = $this->userWithRole('caissier');

        $this->actingAs($user)->post('/caisse/ouvrir', ['solde_ouverture' => 50000])
            ->assertRedirect(route('caisse.index'));

        $this->assertDatabaseHas('caisses', [
            'solde_ouverture' => 50000,
            'statut' => 'ouverte',
            'user_id' => $user->id,
        ]);
    }

    public function test_cannot_open_a_second_caisse_while_one_is_open(): void
    {
        $user = $this->userWithRole('caissier');
        Caisse::factory()->create(['statut' => 'ouverte']);

        $this->actingAs($user)->post('/caisse/ouvrir', ['solde_ouverture' => 10000])
            ->assertSessionHasErrors('caisse');

        $this->assertSame(1, Caisse::count());
    }

    public function test_movement_updates_the_running_balance(): void
    {
        // le DG enregistre directement (les sorties d'un non-DG passent en validation)
        $user = $this->userWithRole('directeur_general');
        $caisse = Caisse::factory()->create(['solde_ouverture' => 100000, 'statut' => 'ouverte']);

        $this->actingAs($user)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'entree', 'libelle' => 'Versement chauffeur', 'montant' => 30000, 'date_mouvement' => now()->toDateString(),
        ])->assertRedirect(route('caisse.index'));

        $this->actingAs($user)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'sortie', 'libelle' => 'Achat fournitures', 'montant' => 20000, 'date_mouvement' => now()->toDateString(),
        ])->assertRedirect(route('caisse.index'));

        // 100 000 + 30 000 − 20 000 = 110 000
        $this->assertEqualsWithDelta(110000, $caisse->fresh()->soldeCourant(), 0.01);
    }

    public function test_cannot_add_movement_to_a_closed_caisse(): void
    {
        $user = $this->userWithRole('caissier');
        $caisse = Caisse::factory()->fermee()->create();

        $this->actingAs($user)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'entree', 'libelle' => 'Test', 'montant' => 5000, 'date_mouvement' => now()->toDateString(),
        ])->assertSessionHasErrors('caisse');

        $this->assertSame(0, MouvementCaisse::count());
    }

    public function test_closing_a_caisse_records_the_final_balance(): void
    {
        $user = $this->userWithRole('caissier');
        $caisse = Caisse::factory()->create(['solde_ouverture' => 100000, 'statut' => 'ouverte']);
        MouvementCaisse::factory()->create(['caisse_id' => $caisse->id, 'type' => 'entree', 'montant' => 50000]);
        MouvementCaisse::factory()->create(['caisse_id' => $caisse->id, 'type' => 'sortie', 'montant' => 15000]);

        $this->actingAs($user)->patch("/caisse/{$caisse->id}/fermer")->assertRedirect(route('caisse.index'));

        $caisse->refresh();
        $this->assertSame('fermee', $caisse->statut);
        // 100 000 + 50 000 − 15 000 = 135 000
        $this->assertEqualsWithDelta(135000, (float) $caisse->solde_fermeture, 0.01);
        $this->assertNotNull($caisse->date_fermeture);
    }

    public function test_caissier_cannot_close_without_permission(): void
    {
        // comptable a caisse.voir seulement (pas fermer)
        $user = $this->userWithRole('comptable');
        $caisse = Caisse::factory()->create(['statut' => 'ouverte']);

        $this->actingAs($user)->patch("/caisse/{$caisse->id}/fermer")->assertForbidden();
        $this->assertDatabaseHas('caisses', ['id' => $caisse->id, 'statut' => 'ouverte']);
    }
}
