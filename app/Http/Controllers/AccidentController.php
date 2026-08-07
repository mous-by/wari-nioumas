<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccidentRequest;
use App\Http\Requests\UpdateAccidentRequest;
use App\Models\Accident;
use App\Models\Chauffeur;
use App\Models\Vehicule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AccidentController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        return view('accidents.index', [
            'accidents' => Accident::with(['vehicule', 'chauffeur'])->latest('date_accident')->latest('id')->get(),
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
            'stats' => [
                'mois' => Accident::whereBetween('date_accident', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count(),
                'annee' => Accident::whereBetween('date_accident', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->count(),
                'en_cours' => Accident::where('statut', 'en_cours')->count(),
                'cout_annee' => (float) Accident::whereBetween('date_accident', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->sum('cout_reparation'),
            ],
        ]);
    }

    public function show(Accident $accident): View
    {
        $accident->load(['vehicule', 'chauffeur', 'user']);

        return view('accidents.show', ['accident' => $accident]);
    }

    public function store(StoreAccidentRequest $request): RedirectResponse
    {
        Accident::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('accidents.index')->with('status', 'Accident enregistré avec succès.');
    }

    public function update(UpdateAccidentRequest $request, Accident $accident): RedirectResponse
    {
        $accident->update($request->validated());

        return redirect()->route('accidents.index')->with('status', 'Accident mis à jour avec succès.');
    }

    public function destroy(Accident $accident): RedirectResponse
    {
        $accident->delete();

        return back()->with('status', 'Accident supprimé avec succès.');
    }
}
