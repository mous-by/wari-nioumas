<?php

namespace App\Http\Controllers;

use App\Models\Chauffeur;
use App\Http\Requests\StoreChauffeurRequest;
use App\Http\Requests\UpdateChauffeurRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChauffeurController extends Controller
{
    public function index(): View
    {
        return view('chauffeurs.index', [
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
            'prochainMatricule' => Chauffeur::genererMatricule(),
        ]);
    }

    public function store(StoreChauffeurRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('chauffeurs', 'public');
        }

        $chauffeur = Chauffeur::create($data);

        $chauffeur->statutHistoriques()->create([
            'ancien_statut' => null,
            'nouveau_statut' => $chauffeur->statut,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('chauffeurs.index')->with('status', 'Chauffeur ajouté avec succès.');
    }

    public function show(Chauffeur $chauffeur): View
    {
        return view('chauffeurs.show', [
            'chauffeur' => $chauffeur,
            'vehiculeActuel' => $chauffeur->vehiculeActuel(),
        ]);
    }

    public function update(UpdateChauffeurRequest $request, Chauffeur $chauffeur): RedirectResponse
    {
        $ancienStatut = $chauffeur->statut;
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            if ($chauffeur->photo) {
                Storage::disk('public')->delete($chauffeur->photo);
            }

            $data['photo'] = $request->file('photo')->store('chauffeurs', 'public');
        }

        $chauffeur->update($data);

        if ($ancienStatut !== $data['statut']) {
            $chauffeur->statutHistoriques()->create([
                'ancien_statut' => $ancienStatut,
                'nouveau_statut' => $data['statut'],
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('chauffeurs.index')->with('status', 'Chauffeur mis à jour avec succès.');
    }

    public function destroy(Chauffeur $chauffeur): RedirectResponse
    {
        // Le chauffeur emporte ses affectations et son historique de statuts.
        DB::transaction(function () use ($chauffeur) {
            $chauffeur->affectations()->delete();
            $chauffeur->statutHistoriques()->delete();
            $chauffeur->delete();
        });

        return back()->with('status', 'Chauffeur et ses affectations supprimés avec succès.');
    }

    public function pdf()
    {
        $pdf = Pdf::loadView('pdf.chauffeurs-liste', [
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('liste-chauffeurs-'.now()->format('Y-m-d').'.pdf');
    }

    public function badge(Chauffeur $chauffeur)
    {
        // Format carte ID standard (CR80, 85.6 x 54 mm) en points, orientation
        // verticale (portrait), recto puis verso. Ne pas repasser 'landscape' en
        // plus du tableau : dompdf inverserait une seconde fois les dimensions.
        $pdf = Pdf::loadView('pdf.chauffeur-badge', [
            'chauffeur' => $chauffeur,
            'qrDataUri' => $this->qrDataUri($chauffeur),
        ])->setPaper([0, 0, 153.07, 242.65]);

        return $pdf->stream('badge-'.$chauffeur->matricule.'.pdf');
    }

    public function badges()
    {
        $chauffeurs = Chauffeur::orderBy('nom')->get();

        $qrByChauffeur = $chauffeurs->mapWithKeys(fn (Chauffeur $c) => [$c->id => $this->qrDataUri($c)]);

        // Planches A4 paysage, 4 colonnes x 2 rangées = 8 badges par page :
        // une planche recto puis une planche verso pour chaque groupe de 8
        // chauffeurs, dans le même ordre/position (découpe alignée recto/verso).
        $sheets = $chauffeurs->chunk(8)->flatMap(fn ($groupe) => [
            ['type' => 'recto', 'chauffeurs' => $groupe],
            ['type' => 'verso', 'chauffeurs' => $groupe],
        ]);

        $pdf = Pdf::loadView('pdf.chauffeurs-badges', [
            'sheets' => $sheets,
            'qrByChauffeur' => $qrByChauffeur,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('badges-chauffeurs-'.now()->format('Y-m-d').'.pdf');
    }

    private function qrDataUri(Chauffeur $chauffeur): string
    {
        $qr = (new Builder(
            writer: new PngWriter(),
            data: "WARI NIOUMA\nChauffeur : {$chauffeur->nom_complet}\nMatricule : {$chauffeur->matricule}",
            size: 240,
            margin: 6,
            foregroundColor: new Color(24, 24, 27),
            backgroundColor: new Color(255, 255, 255),
        ))->build();

        return $qr->getDataUri();
    }
}
