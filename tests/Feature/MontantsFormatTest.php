<?php

namespace Tests\Feature;

use App\Http\Middleware\NormaliserMontants;
use App\Models\AttestationVente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MontantsFormatTest extends TestCase
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
            'type_bien' => 'autre',
            'designation' => 'Alternateur',
            'vendeur_representant' => 'Moustapha Barry',
            'acheteur_nom' => 'Amadou Traoré',
            'montant_total' => 100000,
            'date_vente' => '2026-09-18',
            'lieu_vente' => 'Bamako',
        ], $overrides);
    }

    public function test_amounts_typed_with_spaces_are_accepted_and_stored_as_numbers(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'montant_total' => '3 200 000',
            'montant_paye' => '1 000 000',
            'mode_paiement' => 'especes',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $attestation = AttestationVente::first();
        $this->assertEquals(3200000, $attestation->montant_total);
        $this->assertEquals(1000000, $attestation->montant_paye);
    }

    public function test_non_breaking_and_narrow_spaces_from_the_browser_are_stripped_too(): void
    {
        $dg = $this->userWithRole('directeur_general');

        // U+00A0 et U+202F : séparateurs produits par toLocaleString('fr-FR').
        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'montant_total' => "3\u{00A0}200\u{00A0}000",
            'montant_paye' => "1\u{202F}000\u{202F}000",
            'mode_paiement' => 'especes',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $attestation = AttestationVente::first();
        $this->assertEquals(3200000, $attestation->montant_total);
        $this->assertEquals(1000000, $attestation->montant_paye);
    }

    public function test_decimal_comma_is_converted_to_a_dot(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'montant_total' => '150000,50',
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertEquals(150000.5, AttestationVente::first()->montant_total);
    }

    public function test_a_non_numeric_amount_is_still_rejected(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'montant_total' => 'abc 12',
        ]))->assertSessionHasErrors('montant_total');

        $this->assertSame(0, AttestationVente::count());
    }

    public function test_other_fields_keep_their_spaces(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'designation' => 'Pièce 3 200, modèle A',
            'observations' => 'Payé 1 000 000, reste 2 200 000',
        ]))->assertRedirect();

        $attestation = AttestationVente::first();
        $this->assertSame('Pièce 3 200, modèle A', $attestation->designation);
        $this->assertSame('Payé 1 000 000, reste 2 200 000', $attestation->observations);
    }

    public function test_edit_form_renders_amounts_as_grouped_text_fields(): void
    {
        $dg = $this->userWithRole('directeur_general');
        $attestation = AttestationVente::create($this->payload([
            'user_id' => $dg->id,
            'montant_total' => 3200000,
        ]));

        $html = $this->actingAs($dg)->get(route('attestations.edit', $attestation))
            ->assertOk()
            ->getContent();

        // Plus de type="number" : il affichait "3200000,00" (decimal:2 en locale française).
        $this->assertMatchesRegularExpression('/<input type="text"[^>]*champ-montant[^>]*name="montant_total"/', $html);
        $this->assertDoesNotMatchRegularExpression('/type="number"[^>]*name="montant_total"/', $html);
    }

    public function test_every_money_field_in_the_views_is_declared_in_the_middleware(): void
    {
        $trouves = [];

        foreach (File::allFiles(resource_path('views')) as $fichier) {
            if (! preg_match_all('/<input\b[^>]*\bchamp-montant\b[^>]*>/', $fichier->getContents(), $balises)) {
                continue;
            }
            foreach ($balises[0] as $balise) {
                $this->assertMatchesRegularExpression('/name="([^"]+)"/', $balise, "{$fichier->getRelativePathname()} : champ-montant sans name");
                preg_match('/name="([^"]+)"/', $balise, $m);
                $trouves[$m[1]][] = $fichier->getRelativePathname();
            }
        }

        $this->assertNotEmpty($trouves, 'Aucun champ .champ-montant trouvé : le garde-fou ne vérifie rien.');

        foreach ($trouves as $nom => $fichiers) {
            $this->assertContains(
                $nom,
                NormaliserMontants::CHAMPS,
                "Le champ « {$nom} » (".implode(', ', $fichiers).') doit être ajouté à NormaliserMontants::CHAMPS, sinon "3 200 000" sera rejeté par la validation.'
            );
        }
    }

    public function test_no_amount_input_uses_type_number_step_one_anymore(): void
    {
        foreach (File::allFiles(resource_path('views')) as $fichier) {
            $this->assertStringNotContainsString(
                'type="number" step="1"',
                $fichier->getContents(),
                "{$fichier->getRelativePathname()} : un champ monétaire est repassé en type=\"number\" (affiche 3200000,00). Utiliser la classe champ-montant."
            );
        }
    }
}
