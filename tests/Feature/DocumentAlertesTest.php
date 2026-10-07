<?php

namespace Tests\Feature;

use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Document;
use App\Models\Vehicule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentAlertesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_documents_index_counts_documents_by_statut(): void
    {
        $user = $this->userWithRole('directeur_general');

        Document::factory()->create(['date_expiration' => now()->subDay()]);       // expiré
        Document::factory()->create(['date_expiration' => now()->addDays(5)]);     // proche
        Document::factory()->create(['date_expiration' => now()->addDays(20)]);    // attention
        Document::factory()->create(['date_expiration' => now()->addDays(100)]);   // valide

        $response = $this->actingAs($user)->get('/documents')->assertOk();

        $response->assertViewHas('stats', [
            'expire' => 1,
            'proche' => 1,
            'attention' => 1,
            'valide' => 1,
        ]);
    }

    public function test_caissier_can_view_but_not_manage_documents(): void
    {
        $user = $this->userWithRole('caissier');

        $this->actingAs($user)->get('/documents')->assertOk();
        $this->actingAs($user)->post('/documents', [
            'documentable_type' => 'vehicule',
            'documentable_id' => Vehicule::factory()->create()->id,
            'type_document' => 'assurance',
            'date_expiration' => '2027-01-01',
        ])->assertForbidden();
    }

    public function test_dashboard_shows_expired_and_watched_document_counts(): void
    {
        $user = $this->userWithRole('directeur_general');

        Document::factory()->create(['date_expiration' => now()->subDay()]);
        Document::factory()->create(['date_expiration' => now()->addDays(10)]);
        Document::factory()->create(['date_expiration' => now()->addDays(100)]);

        $response = $this->actingAs($user)->get('/')->assertOk();

        // "documentsExpires" et "documentsASurveiller" sont deux compteurs
        // distincts (voir home.blade.php : "N (M expiré(s))") : le second ne
        // compte que ceux qui ne sont PAS encore expirés (≤ 30 jours restants).
        $response->assertViewHas('documentsExpires', 1);
        $response->assertViewHas('documentsASurveiller', 1);
    }

    public function test_bell_lists_expired_and_soon_expiring_documents(): void
    {
        $user = $this->userWithRole('directeur_general');
        $vehicule = Vehicule::factory()->create(['immatriculation' => 'AB-1234-MD']);
        Document::factory()->create([
            'documentable_type' => Vehicule::class,
            'documentable_id' => $vehicule->id,
            'type_document' => 'assurance',
            'date_expiration' => now()->subDays(3),
        ]);
        Document::factory()->create(['date_expiration' => now()->addDays(200)]); // ne doit pas apparaître

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('AB-1234-MD', $html);
        $this->assertStringContainsString('Document expiré', $html);
    }

    public function test_bell_badge_combines_validations_and_documents(): void
    {
        $user = $this->userWithRole('directeur_general');
        Document::factory()->create(['date_expiration' => now()->subDay()]);
        Document::factory()->create(['date_expiration' => now()->subDays(2)]);

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/badge rounded-pill bg-danger[^>]*>\s*2/', $html);
    }

    public function test_user_without_documents_permission_sees_no_document_alert_in_bell(): void
    {
        // Un utilisateur sans aucune permission "documents" ne doit ni planter
        // ni voir ces alertes (test de robustesse du contrôle d'accès).
        $user = $this->userWithRole('directeur_general');
        $user->revokePermissionTo('documents.voir');
        Document::factory()->create(['date_expiration' => now()->subDay()]);

        $html = $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('Document expiré', $html);
    }

    public function test_affectation_with_expired_vehicule_document_shows_a_warning(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();
        $chauffeur = Chauffeur::factory()->create();
        Document::factory()->create([
            'documentable_type' => Vehicule::class,
            'documentable_id' => $vehicule->id,
            'type_document' => 'assurance',
            'date_expiration' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->post('/affectations', [
            'vehicule_id' => $vehicule->id,
            'chauffeur_id' => $chauffeur->id,
            'montant_journalier' => 5000,
            'periodicite' => 'journalier',
            'date_debut' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('avertissement');
        $this->assertStringContainsString($vehicule->immatriculation, session('avertissement'));
    }

    public function test_affectation_with_expired_permis_shows_a_warning(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();
        $chauffeur = Chauffeur::factory()->create();
        Document::enregistrerPermis($chauffeur, $user->id);
        Document::where('documentable_id', $chauffeur->id)->update(['date_expiration' => now()->subDay()]);

        $response = $this->actingAs($user)->post('/affectations', [
            'vehicule_id' => $vehicule->id,
            'chauffeur_id' => $chauffeur->id,
            'montant_journalier' => 5000,
            'periodicite' => 'journalier',
            'date_debut' => now()->toDateString(),
        ]);

        $response->assertSessionHas('avertissement');
    }

    public function test_affectation_with_valid_documents_shows_no_warning(): void
    {
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();
        $chauffeur = Chauffeur::factory()->create(['permis_date_validite' => now()->addYear()]);

        $response = $this->actingAs($user)->post('/affectations', [
            'vehicule_id' => $vehicule->id,
            'chauffeur_id' => $chauffeur->id,
            'montant_journalier' => 5000,
            'periodicite' => 'journalier',
            'date_debut' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionMissing('avertissement');
    }

    public function test_affectation_does_not_block_when_documents_are_expired(): void
    {
        // L'avertissement est informatif : il ne doit jamais empêcher la
        // création de l'affectation (voir la spécification).
        $user = $this->userWithRole('responsable_parc');
        $vehicule = Vehicule::factory()->create();
        $chauffeur = Chauffeur::factory()->create();
        Document::factory()->create([
            'documentable_type' => Vehicule::class,
            'documentable_id' => $vehicule->id,
            'date_expiration' => now()->subDay(),
        ]);

        $this->actingAs($user)->post('/affectations', [
            'vehicule_id' => $vehicule->id,
            'chauffeur_id' => $chauffeur->id,
            'montant_journalier' => 5000,
            'periodicite' => 'journalier',
            'date_debut' => now()->toDateString(),
        ]);

        $this->assertSame(1, Affectation::where('vehicule_id', $vehicule->id)->where('chauffeur_id', $chauffeur->id)->whereNull('date_fin')->count());
    }
}
