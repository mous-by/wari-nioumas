<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMandatRequest;
use App\Models\Bulletin;
use App\Models\MandatPaiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MandatController extends Controller
{
    public function index(): View
    {
        return view('mandats.index', [
            'mandats' => MandatPaiement::withCount('lignes')->with('signataire')->latest('date_mandat')->latest('id')->get(),
            'moisActuel' => (int) now()->format('n'),
            'anneeActuelle' => (int) now()->format('Y'),
        ]);
    }

    public function store(StoreMandatRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $mois = (int) $data['periode_mois'];
        $annee = (int) $data['periode_annee'];

        // Un mandat paie les bulletins VALIDÉS (et pas déjà payés) de la période.
        $bulletins = Bulletin::with('personnel')
            ->where('periode_mois', $mois)
            ->where('periode_annee', $annee)
            ->where('statut', 'valide')
            ->get();

        if ($bulletins->isEmpty()) {
            return back()->withErrors(['periode' => 'Aucun bulletin validé pour cette période. Validez d\'abord les bulletins.'])->withInput();
        }

        $mandat = DB::transaction(function () use ($data, $mois, $annee, $bulletins) {
            $mandat = MandatPaiement::create([
                'date_mandat' => $data['date_mandat'],
                'banque' => $data['banque'] ?? null,
                'periode_mois' => $mois,
                'periode_annee' => $annee,
                'montant_total' => $bulletins->sum('net_a_payer'),
                'statut' => 'brouillon',
                'observations' => $data['observations'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($bulletins as $bulletin) {
                $mandat->lignes()->create([
                    'personnel_id' => $bulletin->personnel_id,
                    'bulletin_id' => $bulletin->id,
                    'montant' => $bulletin->net_a_payer,
                ]);
            }

            return $mandat;
        });

        return redirect()->route('mandats.show', $mandat)->with('status', 'Mandat de paiement créé avec succès.');
    }

    public function show(MandatPaiement $mandat): View
    {
        $mandat->load(['lignes.personnel', 'signataire']);

        return view('mandats.show', ['mandat' => $mandat]);
    }

    public function signer(MandatPaiement $mandat): RedirectResponse
    {
        if ($mandat->statut !== 'brouillon') {
            return back()->withErrors(['mandat' => 'Ce mandat ne peut plus être signé.']);
        }

        $mandat->update([
            'statut' => 'signe',
            'signataire_id' => auth()->id(),
            'date_signature' => now(),
        ]);

        return back()->with('status', 'Mandat signé avec succès.');
    }

    public function changerStatut(MandatPaiement $mandat): RedirectResponse
    {
        $suivant = match ($mandat->statut) {
            'signe' => 'depose',
            'depose' => 'paye',
            default => null,
        };

        if (! $suivant) {
            return back()->withErrors(['mandat' => 'Transition de statut impossible.']);
        }

        DB::transaction(function () use ($mandat, $suivant) {
            $mandat->update(['statut' => $suivant]);

            // Quand le mandat est payé, on marque les bulletins liés comme payés.
            if ($suivant === 'paye') {
                $bulletinIds = $mandat->lignes()->pluck('bulletin_id')->filter()->all();
                Bulletin::whereIn('id', $bulletinIds)->update(['statut' => 'paye']);
            }
        });

        return back()->with('status', 'Statut du mandat mis à jour : '.MandatPaiement::STATUTS[$suivant].'.');
    }

    public function destroy(MandatPaiement $mandat): RedirectResponse
    {
        $mandat->delete();

        return redirect()->route('mandats.index')->with('status', 'Mandat supprimé avec succès.');
    }

    public function pdf(MandatPaiement $mandat)
    {
        $mandat->load(['lignes.personnel', 'signataire']);

        $pdf = Pdf::loadView('pdf.mandat', ['mandat' => $mandat])->setPaper('a4', 'portrait');

        return $pdf->stream('mandat-'.$mandat->numero.'.pdf');
    }
}
