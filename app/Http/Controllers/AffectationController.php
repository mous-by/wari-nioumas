<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffectationRequest;
use App\Http\Requests\UpdateAffectationRequest;
use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Vehicule;
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
        DB::transaction(function () use ($request) {
            $data = $request->validated();

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
}
