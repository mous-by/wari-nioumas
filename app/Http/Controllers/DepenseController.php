<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepenseRequest;
use App\Http\Requests\UpdateDepenseRequest;
use App\Models\Depense;
use App\Models\Vehicule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DepenseController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        $totalSur = fn ($debut, $fin) => (float) Depense::whereBetween('date_depense', [$debut, $fin])->sum('montant');

        // Répartition par catégorie sur le mois en cours.
        $parCategorie = Depense::whereBetween('date_depense', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->selectRaw('categorie, SUM(montant) as total')
            ->groupBy('categorie')
            ->pluck('total', 'categorie');

        return view('depenses.index', [
            'depenses' => Depense::with('vehicule')->latest('date_depense')->latest('id')->get(),
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
            'totaux' => [
                'semaine' => $totalSur($now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
                'mois' => $totalSur($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
                'annee' => $totalSur($now->copy()->startOfYear(), $now->copy()->endOfYear()),
            ],
            'parCategorie' => $parCategorie,
        ]);
    }

    public function store(StoreDepenseRequest $request): RedirectResponse
    {
        Depense::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('depenses.index')->with('status', 'Dépense enregistrée avec succès.');
    }

    public function update(UpdateDepenseRequest $request, Depense $depense): RedirectResponse
    {
        $depense->update($request->validated());

        return redirect()->route('depenses.index')->with('status', 'Dépense mise à jour avec succès.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        $depense->delete();

        return back()->with('status', 'Dépense supprimée avec succès.');
    }
}
