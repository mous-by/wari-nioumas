<?php

namespace Tests\Feature;

use App\Models\Chauffeur;
use App\Models\Document;
use App\Models\DocumentHistorique;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
        Storage::fake('public');
    }

    public function test_responsable_parc_can_add_a_vehicule_document(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();

        $response = $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => $vehicule->id,
            'type_document' => 'carte_grise',
            'numero' => 'CG-12345',
            'date_etablissement' => '2026-01-10',
            'date_expiration' => '2027-01-10',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'documentable_type' => Vehicule::class,
            'documentable_id' => $vehicule->id,
            'type_document' => 'carte_grise',
            'numero' => 'CG-12345',
        ]);
    }

    public function test_comptable_cannot_add_a_document_read_only_role(): void
    {
        $user = $this->userWithRole('comptable');
        $vehicule = Vehicule::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => $vehicule->id,
            'type_document' => 'assurance',
            'date_expiration' => '2027-01-10',
        ])->assertForbidden();
    }

    public function test_autre_type_requires_a_custom_label_but_no_expiration_date(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => $vehicule->id,
            'type_document' => 'autre',
        ])->assertSessionHasErrors('libelle_autre');

        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => $vehicule->id,
            'type_document' => 'autre',
            'libelle_autre' => 'Certificat de conformité',
        ])->assertSessionHasNoErrors();

        $document = Document::first();
        $this->assertNull($document->date_expiration);
        $this->assertSame('Certificat de conformité', $document->type_libelle);
    }

    public function test_date_expiration_is_required_for_a_standard_vehicule_document(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => $vehicule->id,
            'type_document' => 'assurance',
        ])->assertSessionHasErrors('date_expiration');
    }

    public function test_permis_conduite_type_is_rejected_for_a_vehicule(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();

        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => $vehicule->id,
            'type_document' => 'permis_conduite',
            'date_expiration' => '2027-01-10',
        ])->assertSessionHasErrors('type_document');
    }

    public function test_a_document_can_be_uploaded_and_viewed(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();
        $fichier = UploadedFile::fake()->create('carte-grise.pdf', 500, 'application/pdf');

        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => $vehicule->id,
            'type_document' => 'carte_grise',
            'date_expiration' => '2027-01-10',
            'fichier' => $fichier,
        ])->assertRedirect();

        $document = Document::first();
        $this->assertNotNull($document->fichier);
        Storage::disk('public')->assertExists($document->fichier);
        $this->assertSame('carte-grise.pdf', $document->nom_original);
    }

    public function test_renewing_a_document_logs_history_and_updates_expiration(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $document = Document::factory()->create([
            'type_document' => 'assurance',
            'date_expiration' => '2026-01-01',
        ]);

        $this->actingAs($user)->put("/documents/{$document->id}", [
            'type_document' => 'assurance',
            'numero' => 'ASS-9999',
            'date_expiration' => '2027-06-01',
        ])->assertRedirect();

        $document->refresh();
        $this->assertSame('2027-06-01', $document->date_expiration->toDateString());

        $historique = DocumentHistorique::first();
        $this->assertSame('2026-01-01', $historique->ancienne_echeance->toDateString());
        $this->assertSame('2027-06-01', $historique->nouvelle_echeance->toDateString());
        $this->assertSame($user->id, $historique->user_id);
    }

    public function test_renewal_history_is_shown_on_the_owner_fiche(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();
        $document = Document::factory()->create([
            'documentable_type' => Vehicule::class,
            'documentable_id' => $vehicule->id,
            'type_document' => 'assurance',
            'date_expiration' => '2026-01-01',
        ]);

        // Pas encore d'historique : le bouton ne doit pas apparaître.
        $this->actingAs($user)->get("/vehicules/{$vehicule->id}")
            ->assertOk()
            ->assertDontSee('Historique des renouvellements');

        $this->actingAs($user)->put("/documents/{$document->id}", [
            'type_document' => 'assurance',
            'date_expiration' => '2027-06-01',
        ])->assertRedirect();

        $html = $this->actingAs($user)->get("/vehicules/{$vehicule->id}")->assertOk()->getContent();

        $this->assertStringContainsString('Historique des renouvellements', $html);
        $this->assertStringContainsString('01/01/2026', $html);
        $this->assertStringContainsString('01/06/2027', $html);
    }

    public function test_updating_a_document_without_changing_the_expiration_does_not_log_history(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $document = Document::factory()->create(['date_expiration' => '2026-01-01']);

        $this->actingAs($user)->put("/documents/{$document->id}", [
            'type_document' => $document->type_document,
            'date_expiration' => '2026-01-01',
            'observations' => 'RAS',
        ])->assertRedirect();

        $this->assertSame(0, DocumentHistorique::count());
    }

    public function test_deleting_a_document_removes_its_file(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $fichier = UploadedFile::fake()->create('assurance.pdf', 200, 'application/pdf');

        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => Vehicule::factory()->create()->id,
            'type_document' => 'assurance',
            'date_expiration' => '2027-01-10',
            'fichier' => $fichier,
        ]);

        $document = Document::first();
        $chemin = $document->fichier;

        $this->actingAs($user)->delete("/documents/{$document->id}")->assertRedirect();

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        Storage::disk('public')->assertMissing($chemin);
    }

    // --- Seuils de statut (expiré / proche / attention / valide) ---

    public function test_statut_thresholds_match_the_specification(): void
    {
        $expire = Document::factory()->create(['date_expiration' => now()->subDay()]);
        $proche = Document::factory()->create(['date_expiration' => now()->addDays(7)]);
        $attention = Document::factory()->create(['date_expiration' => now()->addDays(30)]);
        $valide = Document::factory()->create(['date_expiration' => now()->addDays(31)]);
        $sansEcheance = Document::factory()->create(['date_expiration' => null, 'type_document' => 'autre', 'libelle_autre' => 'Contrat']);

        $this->assertSame('expire', $expire->statut);
        $this->assertSame('proche', $proche->statut);
        $this->assertSame('attention', $attention->statut);
        $this->assertSame('valide', $valide->statut);
        $this->assertNull($sansEcheance->statut);
    }

    // --- Permis de conduite (chauffeur) ---

    public function test_creating_a_chauffeur_automatically_creates_its_permis_document(): void
    {
        $user = $this->userWithRole('directeur_general');

        $this->actingAs($user)->post('/chauffeurs', [
            'nom' => 'Traoré',
            'prenom' => 'Ali',
            'telephone' => '70123456',
            'permis_numero' => 'PC-2026-001',
            'permis_date_validite' => '2028-01-01',
            'date_embauche' => '2026-01-01',
            'statut' => 'actif',
        ])->assertRedirect();

        $chauffeur = Chauffeur::where('permis_numero', 'PC-2026-001')->firstOrFail();

        $document = Document::where('documentable_type', Chauffeur::class)
            ->where('documentable_id', $chauffeur->id)
            ->where('type_document', 'permis_conduite')
            ->firstOrFail();

        $this->assertSame('PC-2026-001', $document->numero);
        $this->assertSame('2028-01-01', $document->date_expiration->toDateString());
    }

    public function test_updating_chauffeur_permis_date_syncs_the_document_and_logs_history(): void
    {
        $user = $this->userWithRole('directeur_general');
        $chauffeur = Chauffeur::factory()->create([
            'permis_numero' => 'PC-OLD',
            'permis_date_validite' => '2026-01-01',
        ]);
        // Simule un chauffeur créé avant la fonctionnalité (document absent).
        $this->assertSame(0, Document::count());

        $this->actingAs($user)->put("/chauffeurs/{$chauffeur->id}", [
            'nom' => $chauffeur->nom,
            'prenom' => $chauffeur->prenom,
            'telephone' => $chauffeur->telephone,
            'permis_numero' => 'PC-NEW',
            'permis_date_validite' => '2029-01-01',
            'date_embauche' => $chauffeur->date_embauche->toDateString(),
            'statut' => 'actif',
        ])->assertRedirect();

        $document = Document::where('documentable_id', $chauffeur->id)->where('type_document', 'permis_conduite')->firstOrFail();
        $this->assertSame('PC-NEW', $document->numero);
        $this->assertSame('2029-01-01', $document->date_expiration->toDateString());

        // Pas d'historique : le document n'existait pas encore avant cette modification.
        $this->assertSame(0, DocumentHistorique::count());

        // Un second renouvellement, lui, doit être historisé.
        $this->actingAs($user)->put("/chauffeurs/{$chauffeur->id}", [
            'nom' => $chauffeur->nom,
            'prenom' => $chauffeur->prenom,
            'telephone' => $chauffeur->telephone,
            'permis_numero' => 'PC-NEW',
            'permis_date_validite' => '2030-01-01',
            'date_embauche' => $chauffeur->date_embauche->toDateString(),
            'statut' => 'actif',
        ])->assertRedirect();

        $this->assertSame(1, DocumentHistorique::count());
    }

    public function test_renewing_the_permis_from_the_documents_module_updates_the_chauffeur_fields(): void
    {
        $user = $this->userWithRole('directeur_general');
        $chauffeur = Chauffeur::factory()->create([
            'permis_numero' => 'PC-AAA',
            'permis_date_validite' => '2026-06-01',
        ]);
        Document::enregistrerPermis($chauffeur, $user->id);

        $document = Document::where('documentable_id', $chauffeur->id)->where('type_document', 'permis_conduite')->firstOrFail();

        $this->actingAs($user)->put("/documents/{$document->id}", [
            'type_document' => 'permis_conduite',
            'numero' => 'PC-BBB',
            'date_expiration' => '2031-06-01',
        ])->assertRedirect();

        $chauffeur->refresh();
        $this->assertSame('PC-BBB', $chauffeur->permis_numero);
        $this->assertSame('2031-06-01', $chauffeur->permis_date_validite->toDateString());
    }

    public function test_permis_document_cannot_be_deleted_from_its_own_fiche(): void
    {
        $user = $this->userWithRole('directeur_general');
        $chauffeur = Chauffeur::factory()->create();
        $document = Document::enregistrerPermis($chauffeur, $user->id);

        $html = $this->actingAs($user)->get("/chauffeurs/{$chauffeur->id}")->assertOk()->getContent();

        $this->assertStringNotContainsString("documents/{$document->id}\" class=\"d-inline confirm-form\"", $html);
    }

    // --- Suppression en cascade ---

    public function test_deleting_a_vehicule_also_deletes_its_documents(): void
    {
        $user = $this->userWithRole('directeur_general');
        $vehicule = Vehicule::factory()->create();
        Document::factory()->create(['documentable_type' => Vehicule::class, 'documentable_id' => $vehicule->id]);

        $this->actingAs($user)->delete("/vehicules/{$vehicule->id}")->assertRedirect();

        $this->assertSame(0, Document::where('documentable_id', $vehicule->id)->count());
    }

    public function test_deleting_a_chauffeur_also_deletes_its_documents_and_historiques(): void
    {
        $user = $this->userWithRole('directeur_general');
        $chauffeur = Chauffeur::factory()->create();
        $document = Document::enregistrerPermis($chauffeur, $user->id);
        $document->update(['date_expiration' => now()->addYear()]);
        DocumentHistorique::create(['document_id' => $document->id, 'nouvelle_echeance' => now()->addYear(), 'user_id' => $user->id]);

        $this->actingAs($user)->delete("/chauffeurs/{$chauffeur->id}")->assertRedirect();

        $this->assertSame(0, Document::where('documentable_id', $chauffeur->id)->where('documentable_type', Chauffeur::class)->count());
        $this->assertSame(0, DocumentHistorique::where('document_id', $document->id)->count());
    }
}
