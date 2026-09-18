<?php

namespace Tests\Feature;

use App\Models\AttestationVente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttestationVenteTemoinsTest extends TestCase
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

    private function pdfHtml(AttestationVente $attestation): string
    {
        return view('pdf.attestation-vente', ['attestation' => $attestation])->render();
    }

    public function test_temoins_are_off_by_default_and_absent_from_the_pdf(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload())->assertRedirect();

        $attestation = AttestationVente::first();
        $this->assertFalse($attestation->avec_temoins);

        $html = $this->pdfHtml($attestation);
        $this->assertStringNotContainsString('Signature du témoin', $html);
        // Le mot apparaît dans un commentaire CSS : on vérifie le vrai bloc.
        $this->assertStringNotContainsString('class="temoins-title"', $html);
    }

    public function test_temoins_with_names_appear_with_their_signature_boxes(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'avec_temoins' => '1',
            'temoin_1_nom' => 'Moussa Keita',
            'temoin_2_nom' => 'Fanta Coulibaly',
        ]))->assertRedirect();

        $attestation = AttestationVente::first();
        $this->assertTrue($attestation->avec_temoins);

        $html = $this->pdfHtml($attestation);
        $this->assertStringContainsString('Signature du témoin 1 — Moussa Keita', $html);
        $this->assertStringContainsString('Signature du témoin 2 — Fanta Coulibaly', $html);
    }

    public function test_temoins_enabled_with_blank_names_still_prints_empty_signature_boxes(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'avec_temoins' => '1',
        ]))->assertRedirect();

        $html = $this->pdfHtml(AttestationVente::first());

        $this->assertStringContainsString('Signature du témoin 1', $html);
        $this->assertStringContainsString('Signature du témoin 2', $html);
        $this->assertStringNotContainsString('Signature du témoin 1 —', $html);
    }

    public function test_unchecking_temoins_on_update_removes_the_block(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'avec_temoins' => '1',
            'temoin_1_nom' => 'Moussa Keita',
        ]))->assertRedirect();

        $attestation = AttestationVente::first();
        $this->assertTrue($attestation->avec_temoins);

        // La case décochée envoie le champ caché "0" (voir _fields.blade.php).
        $this->actingAs($dg)->put("/configuration/attestations/{$attestation->id}", $this->payload([
            'avec_temoins' => '0',
            'temoin_1_nom' => 'Moussa Keita',
        ]))->assertRedirect();

        $attestation->refresh();
        $this->assertFalse($attestation->avec_temoins);
        $this->assertStringNotContainsString('Signature du témoin', $this->pdfHtml($attestation));
    }

    public function test_show_page_lists_temoins_only_when_enabled(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $sans = AttestationVente::create($this->payload(['user_id' => $dg->id]));
        $avec = AttestationVente::create($this->payload([
            'user_id' => $dg->id,
            'avec_temoins' => true,
            'temoin_1_nom' => 'Moussa Keita',
        ]));

        $this->actingAs($dg)->get("/configuration/attestations/{$sans->id}")
            ->assertOk()
            ->assertDontSee('Témoin 1');

        $this->actingAs($dg)->get("/configuration/attestations/{$avec->id}")
            ->assertOk()
            ->assertSee('Témoin 1')
            ->assertSee('Moussa Keita');
    }

    public function test_temoin_name_is_length_limited(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/configuration/attestations', $this->payload([
            'avec_temoins' => '1',
            'temoin_1_nom' => str_repeat('a', 256),
        ]))->assertSessionHasErrors('temoin_1_nom');

        $this->assertSame(0, AttestationVente::count());
    }
}
