<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\MouvementCaisse;
use App\Models\Validation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_caissier_sortie_creates_a_pending_validation_not_a_movement(): void
    {
        $user = $this->userWithRole('caissier');
        $caisse = Caisse::factory()->create(['statut' => 'ouverte']);

        $this->actingAs($user)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'sortie', 'libelle' => 'Achat pièces', 'montant' => 40000, 'date_mouvement' => now()->toDateString(),
        ])->assertRedirect(route('caisse.index'));

        $this->assertSame(0, MouvementCaisse::count());
        $this->assertDatabaseHas('validations', [
            'type' => 'caisse.sortie',
            'statut' => 'en_attente',
            'demandeur_id' => $user->id,
        ]);
    }

    public function test_caissier_entree_is_recorded_directly(): void
    {
        $user = $this->userWithRole('caissier');
        $caisse = Caisse::factory()->create(['statut' => 'ouverte']);

        $this->actingAs($user)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'entree', 'libelle' => 'Recette', 'montant' => 40000, 'date_mouvement' => now()->toDateString(),
        ])->assertRedirect(route('caisse.index'));

        // une entrée ne nécessite pas de validation
        $this->assertSame(1, MouvementCaisse::count());
        $this->assertSame(0, Validation::count());
    }

    public function test_directeur_general_sortie_is_recorded_directly(): void
    {
        $user = $this->userWithRole('directeur_general');
        $caisse = Caisse::factory()->create(['statut' => 'ouverte']);

        $this->actingAs($user)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'sortie', 'libelle' => 'Sortie DG', 'montant' => 40000, 'date_mouvement' => now()->toDateString(),
        ]);

        $this->assertSame(1, MouvementCaisse::count());
        $this->assertSame(0, Validation::count());
    }

    public function test_non_dg_cannot_access_validations_page(): void
    {
        $user = $this->userWithRole('caissier');

        $this->actingAs($user)->get('/validations')->assertForbidden();
    }

    public function test_dg_approving_executes_the_action(): void
    {
        $caissier = $this->userWithRole('caissier');
        $dg = $this->userWithRole('directeur_general');
        $caisse = Caisse::factory()->create(['solde_ouverture' => 100000, 'statut' => 'ouverte']);

        // le caissier demande une sortie
        $this->actingAs($caissier)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'sortie', 'libelle' => 'Achat', 'montant' => 30000, 'date_mouvement' => now()->toDateString(),
        ]);
        $validation = Validation::first();
        $this->assertSame(0, MouvementCaisse::count());

        // le DG approuve -> la sortie est exécutée
        $this->actingAs($dg)->patch("/validations/{$validation->id}/approuver")->assertRedirect();

        $this->assertSame('approuvee', $validation->fresh()->statut);
        $this->assertSame(1, MouvementCaisse::count());
        $this->assertEqualsWithDelta(70000, $caisse->fresh()->soldeCourant(), 0.01);
    }

    public function test_dg_refusing_does_not_execute(): void
    {
        $caissier = $this->userWithRole('caissier');
        $dg = $this->userWithRole('directeur_general');
        $caisse = Caisse::factory()->create(['statut' => 'ouverte']);

        $this->actingAs($caissier)->post("/caisse/{$caisse->id}/mouvements", [
            'type' => 'sortie', 'libelle' => 'Achat', 'montant' => 30000, 'date_mouvement' => now()->toDateString(),
        ]);
        $validation = Validation::first();

        $this->actingAs($dg)->patch("/validations/{$validation->id}/refuser", ['motif' => 'Non justifié'])->assertRedirect();

        $this->assertSame('refusee', $validation->fresh()->statut);
        $this->assertSame(0, MouvementCaisse::count());
    }
}
