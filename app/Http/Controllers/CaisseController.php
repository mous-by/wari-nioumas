<?php

namespace App\Http\Controllers;

use App\Http\Requests\OuvrirCaisseRequest;
use App\Http\Requests\StoreMouvementCaisseRequest;
use App\Models\Caisse;
use App\Models\MouvementCaisse;
use App\Support\Approbation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CaisseController extends Controller
{
    public function index(): View
    {
        $caisseOuverte = Caisse::ouverte()->with('mouvements.user')->latest('date_ouverture')->first();

        return view('caisse.index', [
            'caisseOuverte' => $caisseOuverte,
            'historique' => Caisse::where('statut', 'fermee')->with('user')->latest('date_ouverture')->get(),
        ]);
    }

    public function ouvrir(OuvrirCaisseRequest $request): RedirectResponse
    {
        if (Caisse::ouverte()->exists()) {
            return back()->withErrors(['caisse' => 'Une caisse est déjà ouverte. Fermez-la avant d\'en ouvrir une nouvelle.']);
        }

        Caisse::create([
            ...$request->validated(),
            'date_ouverture' => now(),
            'statut' => 'ouverte',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('caisse.index')->with('status', 'Caisse ouverte avec succès.');
    }

    public function mouvement(StoreMouvementCaisseRequest $request, Caisse $caisse): RedirectResponse
    {
        if (! $caisse->estOuverte()) {
            return back()->withErrors(['caisse' => 'Cette caisse est fermée : aucun mouvement ne peut y être ajouté.']);
        }

        $data = $request->validated();

        // Toute SORTIE d'argent par un rôle autre que le DG doit être validée par lui.
        if ($data['type'] === 'sortie' && ! Approbation::estValideur(auth()->user())) {
            Approbation::demander(
                'caisse.sortie',
                'Sortie de caisse : '.$data['libelle'].' ('.number_format((float) $data['montant'], 0, ',', ' ').' FCFA)',
                [...$data, 'caisse_id' => $caisse->id],
            );

            return redirect()->route('caisse.index')->with('status', 'Demande de sortie envoyée au Directeur général pour validation.');
        }

        $caisse->mouvements()->create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('caisse.index')->with('status', 'Mouvement enregistré avec succès.');
    }

    public function fermer(Caisse $caisse): RedirectResponse
    {
        if (! $caisse->estOuverte()) {
            return back()->withErrors(['caisse' => 'Cette caisse est déjà fermée.']);
        }

        $caisse->update([
            'solde_fermeture' => $caisse->soldeCourant(),
            'date_fermeture' => now(),
            'statut' => 'fermee',
        ]);

        return redirect()->route('caisse.index')->with('status', 'Caisse fermée. Solde final : '.number_format($caisse->solde_fermeture, 0, ',', ' ').' FCFA.');
    }

    public function destroyMouvement(MouvementCaisse $mouvement): RedirectResponse
    {
        if ($mouvement->estAutomatique()) {
            return back()->withErrors(['caisse' => 'Ce mouvement provient automatiquement d\'un versement ou d\'une dépense : supprimez la source concernée.']);
        }

        if (! $mouvement->caisse->estOuverte()) {
            return back()->withErrors(['caisse' => 'Impossible de supprimer un mouvement d\'une caisse fermée.']);
        }

        $mouvement->delete();

        return back()->with('status', 'Mouvement supprimé.');
    }
}
