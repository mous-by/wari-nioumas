<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffectationRequest;
use App\Http\Requests\StoreVoyageRequest;
use App\Http\Requests\UpdateAffectationRequest;
use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Vehicule;
use App\Models\Versement;
use App\Models\Voyage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AffectationController extends Controller
{
    public function index(): View
    {
        return view('affectations.index', [
            'affectations' => Affectation::with(['vehicule', 'chauffeur'])->latest('date_debut')->get(),
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
        ]);
    }

    public function store(StoreAffectationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Un double-clic sur « Enregistrer » soumet deux fois le même
        // formulaire : sans ce garde-fou, la 2e soumission referme aussitôt
        // l'affectation tout juste créée par la 1re (même véhicule +
        // chauffeur déjà actifs) et en recrée une identique, laissant une
        // ligne fantôme "ouverte puis fermée le même jour" dans l'historique.
        $dejaActive = Affectation::where('vehicule_id', $data['vehicule_id'])
            ->where('chauffeur_id', $data['chauffeur_id'])
            ->whereNull('date_fin')
            ->exists();

        if ($dejaActive) {
            return redirect()->route('affectations.index')->with('status', 'Cette affectation est déjà active.');
        }

        DB::transaction(function () use ($data) {
            Affectation::where('vehicule_id', $data['vehicule_id'])
                ->whereNull('date_fin')
                ->update(['date_fin' => $data['date_debut'], 'motif_fin' => 'Réaffecté à un autre chauffeur']);

            Affectation::where('chauffeur_id', $data['chauffeur_id'])
                ->whereNull('date_fin')
                ->update(['date_fin' => $data['date_debut'], 'motif_fin' => 'Affecté à un autre véhicule']);

            Affectation::create([...$data, 'user_id' => auth()->id()]);
        });

        return redirect()->route('affectations.index')->with('status', 'Affectation enregistrée avec succès.');
    }

    public function update(UpdateAffectationRequest $request, Affectation $affectation): RedirectResponse
    {
        $affectation->update($request->validated());

        return redirect()->route('affectations.index')->with('status', 'Affectation mise à jour avec succès.');
    }

    public function terminer(Affectation $affectation): RedirectResponse
    {
        $affectation->update([
            'date_fin' => now(),
            'motif_fin' => 'Terminée manuellement',
        ]);

        return back()->with('status', 'Affectation terminée avec succès.');
    }

    public function destroy(Affectation $affectation): RedirectResponse
    {
        $affectation->delete();

        return back()->with('status', 'Affectation supprimée avec succès.');
    }

    /**
     * Ajoute un voyage (date + montant) à une affectation « voyage ». Son
     * montant s'accumule automatiquement au total du chauffeur, ET crée un
     * versement classique (chauffeur + date + montant) pour que l'argent
     * entre en Caisse et apparaisse dans l'historique des versements de la
     * page Recettes, comme un versement journalier/mensuel.
     */
    public function ajouterVoyage(StoreVoyageRequest $request, Affectation $affectation): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $affectation) {
            Voyage::create([
                ...$data,
                'affectation_id' => $affectation->id,
                'user_id' => auth()->id(),
            ]);

            Versement::create([
                'chauffeur_id' => $affectation->chauffeur_id,
                'date_versement' => $data['date_voyage'],
                'montant' => $data['montant'],
                'observations' => trim('Voyage — '.($affectation->vehicule?->immatriculation ?? '').' '.($data['observations'] ?? '')),
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('affectations.index')->with('status', 'Voyage enregistré avec succès.');
    }
}
