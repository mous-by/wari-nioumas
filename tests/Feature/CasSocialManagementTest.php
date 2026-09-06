<?php

namespace Tests\Feature;

use App\Models\Caisse;
use App\Models\CasSocial;
use App\Models\MouvementCaisse;
use App\Models\Personnel;
use App\Models\TypeCasSocial;
use App\Support\CaisseAuto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasSocialManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_numero_format_and_uniqueness(): void
    {
        $premier = CasSocial::factory()->create();
        $deuxieme = CasSocial::factory()->create();

        $this->assertMatchesRegularExpression('/^CS-\d{4}-\d{6}$/', $premier->numero);
        $this->assertNotSame($premier->numero, $deuxieme->numero);
    }

    public function test_creation_soumission_et_approbation_ne_creent_jamais_de_mouvement(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);

        $this->assertSame('approuve', $cas->fresh()->statut);
        $this->assertSame(0, MouvementCaisse::count());
    }

    public function test_payer_cree_exactement_une_sortie_du_bon_montant(): void
    {
        $caisse = Caisse::factory()->create(['solde_ouverture' => 500000, 'statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $type = TypeCasSocial::factory()->create(['libelle' => 'Mariage']);
        $personnel = Personnel::factory()->create(['nom' => 'Traoré', 'prenom' => 'Mamadou']);
        $cas = CasSocial::factory()->create([
            'type_cas_social_id' => $type->id,
            'personnel_id' => $personnel->id,
            'montant_demande' => 100000,
        ]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);

        $cas->payer($caissier);

        $this->assertSame('paye', $cas->fresh()->statut);
        $this->assertSame(1, MouvementCaisse::where('source_type', CasSocial::class)->where('source_id', $cas->id)->count());
        $this->assertDatabaseHas('mouvement_caisses', [
            'caisse_id' => $caisse->id,
            'type' => 'sortie',
            'montant' => 100000,
            'source_type' => CasSocial::class,
            'source_id' => $cas->id,
        ]);
        $this->assertEqualsWithDelta(400000, $caisse->fresh()->soldeCourant(), 0.01);
    }

    public function test_payer_deux_fois_ne_cree_jamais_une_deuxieme_sortie(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);
        $cas->payer($caissier);

        $this->expectException(\RuntimeException::class);
        $cas->payer($caissier);
    }

    public function test_payer_echoue_sans_caisse_ouverte(): void
    {
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);

        $this->expectException(\RuntimeException::class);
        $cas->payer($caissier);

        $this->assertSame(0, MouvementCaisse::count());
    }

    public function test_utilisateur_sans_droit_payer_ne_peut_pas_declencher_le_paiement(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $gestionnaire = $this->userWithRole('gestionnaire'); // n'a pas cas_sociaux.payer

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);

        $this->actingAs($gestionnaire)
            ->patch(route('cas-sociaux.payer', $cas))
            ->assertForbidden();

        $this->assertSame(0, MouvementCaisse::count());
        $this->assertSame('approuve', $cas->fresh()->statut);
    }

    public function test_annuler_un_cas_paye_cree_une_contrepassation_sans_toucher_loriginal(): void
    {
        $caisse = Caisse::factory()->create(['solde_ouverture' => 500000, 'statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);
        $cas->payer($caissier);

        $original = MouvementCaisse::where('source_type', CasSocial::class)->where('source_id', $cas->id)->first();

        $cas->annuler($dg, 'Erreur de saisie');

        $this->assertSame('annule', $cas->fresh()->statut);
        $this->assertSame(2, MouvementCaisse::count());

        $original->refresh();
        $this->assertSame('sortie', $original->type);
        $this->assertEqualsWithDelta(100000, (float) $original->montant, 0.01);
        $this->assertNull($original->reversal_of_id);

        $reversal = $original->reversal;
        $this->assertNotNull($reversal);
        $this->assertSame('entree', $reversal->type);
        $this->assertEqualsWithDelta(100000, (float) $reversal->montant, 0.01);

        // Le solde revient exactement à l'identique.
        $this->assertEqualsWithDelta(500000, $caisse->fresh()->soldeCourant(), 0.01);
    }

    public function test_annuler_deux_fois_ne_cree_pas_une_deuxieme_contrepassation(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);
        $cas->payer($caissier);
        $cas->annuler($dg, 'Erreur de saisie');

        $this->expectException(\RuntimeException::class);
        $cas->annuler($dg, 'Deuxième tentative');
    }

    public function test_annuler_un_cas_paye_exige_le_droit_de_payer(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);
        $cas->payer($caissier);

        // Le comptable a cas_sociaux.approuver ? Non — il ne l'a pas non plus,
        // donc on utilise un DG sans droit de payer pour isoler le test : ici
        // on simule via un utilisateur qui a approuver mais pas payer.
        $approbateurSeul = $this->userWithRole('directeur_general');
        $approbateurSeul->revokePermissionTo('cas_sociaux.payer');

        $this->actingAs($approbateurSeul)
            ->patch(route('cas-sociaux.annuler', $cas), ['motif' => 'Test'])
            ->assertSessionHasErrors('statut');

        $this->assertSame('paye', $cas->fresh()->statut);
        $this->assertSame(1, MouvementCaisse::count());
    }

    public function test_navigation_cas_vers_mouvement_et_mouvement_vers_cas(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);
        $cas->payer($caissier);

        $mouvement = $cas->mouvements()->first();

        $this->assertNotNull($mouvement);
        $this->assertTrue($mouvement->source->is($cas->fresh()));
    }

    public function test_un_type_ne_peut_jamais_etre_supprime_seulement_desactive(): void
    {
        $type = TypeCasSocial::factory()->create(['actif' => true]);
        CasSocial::factory()->create(['type_cas_social_id' => $type->id]);

        $type->update(['actif' => false]);

        $this->assertDatabaseHas('type_cas_sociaux', ['id' => $type->id, 'actif' => false]);
        $this->assertDatabaseHas('cas_sociaux', ['type_cas_social_id' => $type->id]);
    }

    public function test_les_pages_principales_saffichent_sans_erreur(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);
        $cas->payer($caissier);
        $cas->annuler($dg, 'Test');

        $this->actingAs($dg)->get(route('cas-sociaux.index'))->assertOk();
        $this->actingAs($dg)->get(route('cas-sociaux.show', $cas))->assertOk();
        $this->actingAs($dg)->get(route('cas-sociaux.create'))->assertOk();
        $this->actingAs($dg)->get(route('cas-sociaux.types.index'))->assertOk();
        $this->actingAs($dg)->get(route('cas-sociaux.pdf', $cas))->assertOk();
        $this->actingAs($dg)->get(route('personnel.show', $cas->personnel))->assertOk();
    }

    public function test_contrepasser_est_idempotent(): void
    {
        Caisse::factory()->create(['statut' => 'ouverte']);
        $dg = $this->userWithRole('directeur_general');
        $caissier = $this->userWithRole('caissier');

        $cas = CasSocial::factory()->create(['montant_demande' => 100000]);
        $cas->soumettre();
        $cas->approuver($dg, 100000);
        $cas->payer($caissier);

        $original = $cas->mouvements()->first();

        $premiere = CaisseAuto::contrepasser($original, 'Annulation test', $dg->id);
        $deuxieme = CaisseAuto::contrepasser($original->fresh(), 'Annulation test bis', $dg->id);

        $this->assertSame($premiere->id, $deuxieme->id);
        $this->assertSame(2, MouvementCaisse::count());
    }
}
