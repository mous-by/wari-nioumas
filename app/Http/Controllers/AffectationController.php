<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffectationRequest;
use App\Http\Requests\StoreAffectationVersementRequest;
use App\Http\Requests\UpdateAffectationRequest;
use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Vehicule;
use App\Models\Versement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AffectationController extends Controller
{
    public function index(): View
    {
        return view('affectations.index', [
            'affectations' => Affectation::with(['vehicule', 'chauffeur', 'versements'])->latest('date_debut')->get(),
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
     * Enregistre un versement pour une affectation « voyage » : rattaché à
     * l'affectation (reliquat propre à ce voyage) ET au chauffeur (compte
     * aussi dans le total global de la page Recettes).
     */
    public function verser(StoreAffectationVersementRequest $request, Affectation $affectation): RedirectResponse
    {
        Versement::create([
            ...$request->validated(),
            'chauffeur_id' => $affectation->chauffeur_id,
            'affectation_id' => $affectation->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('affectations.index')->with('status', 'Versement enregistré avec succès.');
    }
}
