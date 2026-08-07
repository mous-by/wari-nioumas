<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use App\Models\Chauffeur;
use App\Models\Incident;
use App\Models\Vehicule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        return view('incidents.index', [
            'incidents' => Incident::with(['vehicule', 'chauffeur'])->latest('date_incident')->latest('id')->get(),
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
            'stats' => [
                'mois' => Incident::whereBetween('date_incident', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count(),
                'annee' => Incident::whereBetween('date_incident', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->count(),
                'ouverts' => Incident::where('statut', 'ouvert')->count(),
                'cout_annee' => (float) Incident::whereBetween('date_incident', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->sum('cout'),
            ],
        ]);
    }

    public function show(Incident $incident): View
    {
        $incident->load(['vehicule', 'chauffeur', 'user']);

        return view('incidents.show', ['incident' => $incident]);
    }

    public function store(StoreIncidentRequest $request): RedirectResponse
    {
        Incident::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('incidents.index')->with('status', 'Incident enregistré avec succès.');
    }

    public function update(UpdateIncidentRequest $request, Incident $incident): RedirectResponse
    {
        $incident->update($request->validated());

        return redirect()->route('incidents.index')->with('status', 'Incident mis à jour avec succès.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $incident->delete();

        return back()->with('status', 'Incident supprimé avec succès.');
    }
}
