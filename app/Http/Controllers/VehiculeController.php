<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehiculeRequest;
use App\Http\Requests\UpdateVehiculeRequest;
use App\Models\Vehicule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VehiculeController extends Controller
{
    public function index(): View
    {
        return view('vehicules.index', [
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
        ]);
    }

    public function store(StoreVehiculeRequest $request): RedirectResponse
    {
        $vehicule = Vehicule::create($request->validated());

        $vehicule->etatHistoriques()->create([
            'ancien_etat' => null,
            'nouveau_etat' => $vehicule->etat,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('vehicules.index')->with('status', 'Véhicule ajouté avec succès.');
    }

    public function show(Vehicule $vehicule): View
    {
        return view('vehicules.show', [
            'vehicule' => $vehicule,
            'chauffeurActuel' => $vehicule->chauffeurActuel(),
        ]);
    }

    public function update(UpdateVehiculeRequest $request, Vehicule $vehicule): RedirectResponse
    {
        $ancienEtat = $vehicule->etat;
        $data = $request->validated();

        $vehicule->update($data);

        if ($ancienEtat !== $data['etat']) {
            $vehicule->etatHistoriques()->create([
                'ancien_etat' => $ancienEtat,
                'nouveau_etat' => $data['etat'],
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('vehicules.index')->with('status', 'Véhicule mis à jour avec succès.');
    }

    public function destroy(Vehicule $vehicule): RedirectResponse
    {
        // Le véhicule emporte avec lui ses affectations et son historique d'états,
        // pour ne laisser aucune référence orpheline (ex. page Affectations).
        DB::transaction(function () use ($vehicule) {
            $vehicule->affectations()->delete();
            $vehicule->etatHistoriques()->delete();
            $vehicule->delete();
        });

        return back()->with('status', 'Véhicule et ses affectations supprimés avec succès.');
    }
}
