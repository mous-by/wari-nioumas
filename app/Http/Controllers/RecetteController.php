<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVersementRequest;
use App\Http\Requests\UpdateVersementRequest;
use App\Models\Chauffeur;
use App\Models\Versement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class RecetteController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $debutSemestre = $now->month <= 6
            ? $now->copy()->startOfYear()
            : $now->copy()->startOfYear()->addMonths(6);

        $verseSur = fn ($debut, $fin) => (float) Versement::whereBetween('date_versement', [$debut, $fin])->sum('montant');

        // Un chauffeur dont TOUTES les affectations sont de type "voyage" n'a
        // aucun montant dû ici (voir Chauffeur::totalVoyages() sur la page
        // Affectations à la place) : on ne l'affiche pas, pour éviter de
        // confondre un versement Recettes avec le total de ses voyages.
        $nApasQueDesVoyages = fn (Chauffeur $chauffeur) => $chauffeur->affectations->isEmpty()
            || $chauffeur->affectations->contains(fn ($a) => $a->periodicite !== 'voyage');

        // Comptes des chauffeurs : dû (accumulé), versé, solde.
        $comptes = Chauffeur::with(['affectations', 'absences', 'versements'])
            ->orderBy('nom')
            ->get()
            ->filter($nApasQueDesVoyages)
            ->map(function (Chauffeur $chauffeur) {
                $du = $chauffeur->montantDu();
                $verse = $chauffeur->totalVerse();

                return [
                    'chauffeur' => $chauffeur,
                    'montant_journalier' => $chauffeur->montantJournalierActuel(),
                    'periodicite_suffixe' => $chauffeur->periodiciteActuelleSuffixe(),
                    'du' => $du,
                    'verse' => $verse,
                    'solde' => $du - $verse,
                ];
            });

        return view('recettes.index', [
            'comptes' => $comptes,
            'versements' => Versement::with('chauffeur')->latest('date_versement')->latest('id')->get(),
            'chauffeurs' => Chauffeur::where('statut', 'actif')->with('affectations')->orderBy('nom')->get()->filter($nApasQueDesVoyages)->values(),
            'totaux' => [
                'semaine' => $verseSur($now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
                'mois' => $verseSur($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
                'semestre' => $verseSur($debutSemestre, $debutSemestre->copy()->addMonths(6)->subDay()),
                'annee' => $verseSur($now->copy()->startOfYear(), $now->copy()->endOfYear()),
            ],
            'duGlobal' => $comptes->sum('du'),
            'verseGlobal' => $comptes->sum('verse'),
            'soldeGlobal' => $comptes->sum('solde'),
        ]);
    }

    public function store(StoreVersementRequest $request): RedirectResponse
    {
        Versement::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('recettes.index')->with('status', 'Versement enregistré avec succès.');
    }

    public function update(UpdateVersementRequest $request, Versement $versement): RedirectResponse
    {
        $versement->update($request->validated());

        return redirect()->route('recettes.index')->with('status', 'Versement mis à jour avec succès.');
    }

    public function destroy(Versement $versement): RedirectResponse
    {
        $versement->delete();

        return back()->with('status', 'Versement supprimé avec succès.');
    }
}
