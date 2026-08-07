<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Requests\UpdateAbsenceRequest;
use App\Models\Absence;
use App\Models\Chauffeur;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AbsenceController extends Controller
{
    public function index(): View
    {
        return view('absences.index', [
            'absences' => Absence::with(['chauffeur', 'validateur'])->latest('date_debut')->get(),
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
        ]);
    }

    public function store(StoreAbsenceRequest $request): RedirectResponse
    {
        Absence::create([...$request->validated(), 'user_id' => auth()->id()]);

        return redirect()->route('absences.index')->with('status', 'Absence enregistrée avec succès.');
    }

    public function update(UpdateAbsenceRequest $request, Absence $absence): RedirectResponse
    {
        $absence->update($request->validated());

        return redirect()->route('absences.index')->with('status', 'Absence mise à jour avec succès.');
    }

    public function accepter(Absence $absence): RedirectResponse
    {
        // Les jours d'absence acceptée sont automatiquement déduits du montant
        // dû du chauffeur (voir Chauffeur::montantDu). Il suffit de valider.
        $absence->update([
            'statut' => 'acceptee',
            'validee_par' => auth()->id(),
        ]);

        return back()->with('status', 'Absence acceptée. Les jours concernés seront déduits du montant dû.');
    }

    public function refuser(Absence $absence): RedirectResponse
    {
        $absence->update([
            'statut' => 'refusee',
            'validee_par' => auth()->id(),
        ]);

        return back()->with('status', 'Absence refusée.');
    }

    public function destroy(Absence $absence): RedirectResponse
    {
        $absence->delete();

        return back()->with('status', 'Absence supprimée avec succès.');
    }
}
