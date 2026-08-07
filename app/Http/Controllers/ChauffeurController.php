<?php

namespace App\Http\Controllers;

use App\Models\Chauffeur;
use App\Http\Requests\StoreChauffeurRequest;
use App\Http\Requests\UpdateChauffeurRequest;
use Illuminate\Http\RedirectResponse;
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
        $chauffeur = Chauffeur::create($request->validated());

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
        $data = $request->validated();

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
        $chauffeur->delete();

        return back()->with('status', 'Chauffeur supprimé avec succès.');
    }
}
