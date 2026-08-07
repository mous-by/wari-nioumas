<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\Chauffeur;
use App\Models\Depense;
use App\Models\MouvementCaisse;
use App\Models\Versement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaisseAutoFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_versement_feeds_an_open_caisse_as_entree(): void
    {
        $caisse = Caisse::factory()->create(['solde_ouverture' => 0, 'statut' => 'ouverte']);
        $chauffeur = Chauffeur::factory()->create();

        $versement = Versement::create([
            'chauffeur_id' => $chauffeur->id,
            'date_versement' => now()->toDateString(),
            'montant' => 30000,
        ]);

        $this->assertDatabaseHas('mouvement_caisses', [
            'caisse_id' => $caisse->id,
            'type' => 'entree',
            'montant' => 30000,
            'source_type' => Versement::class,
            'source_id' => $versement->id,
        ]);
        $this->assertEqualsWithDelta(30000, $caisse->fresh()->soldeCourant(), 0.01);
    }

    public function test_depense_feeds_an_open_caisse_as_sortie(): void
    {
        $caisse = Caisse::factory()->create(['solde_ouverture' => 100000, 'statut' => 'ouverte']);

        Depense::factory()->create(['montant' => 25000, 'categorie' => 'carburant']);

        $this->assertDatabaseHas('mouvement_caisses', [
            'caisse_id' => $caisse->id,
            'type' => 'sortie',
            'montant' => 25000,
        ]);
        // 100 000 − 25 000 = 75 000
        $this->assertEqualsWithDelta(75000, $caisse->fresh()->soldeCourant(), 0.01);
    }

    public function test_no_movement_created_when_no_caisse_is_open(): void
    {
        Caisse::factory()->fermee()->create();

        Versement::create([
            'chauffeur_id' => Chauffeur::factory()->create()->id,
            'date_versement' => now()->toDateString(),
            'montant' => 15000,
        ]);

        $this->assertSame(0, MouvementCaisse::count());
    }

    public function test_updating_source_updates_the_linked_movement(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $versement = Versement::create([
            'chauffeur_id' => Chauffeur::factory()->create()->id,
            'date_versement' => now()->toDateString(),
            'montant' => 20000,
        ]);

        $versement->update(['montant' => 45000]);

        $this->assertSame(1, MouvementCaisse::count());
        $this->assertDatabaseHas('mouvement_caisses', ['source_id' => $versement->id, 'montant' => 45000]);
    }

    public function test_deleting_source_removes_the_linked_movement(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $versement = Versement::create([
            'chauffeur_id' => Chauffeur::factory()->create()->id,
            'date_versement' => now()->toDateString(),
            'montant' => 20000,
        ]);

        $this->assertSame(1, MouvementCaisse::count());

        $versement->delete();

        $this->assertSame(0, MouvementCaisse::count());
    }

    public function test_automatic_movement_cannot_be_deleted_manually(): void
    {
        $user = $this->userWithRole('caissier');
        Caisse::factory()->create(['statut' => 'ouverte']);
        $versement = Versement::create([
            'chauffeur_id' => Chauffeur::factory()->create()->id,
            'date_versement' => now()->toDateString(),
            'montant' => 20000,
        ]);
        $mouvement = MouvementCaisse::first();

        $this->actingAs($user)->delete("/caisse/mouvements/{$mouvement->id}")->assertSessionHasErrors('caisse');
        $this->assertDatabaseHas('mouvement_caisses', ['id' => $mouvement->id]);
    }
}
