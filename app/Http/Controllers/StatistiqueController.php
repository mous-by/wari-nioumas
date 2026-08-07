<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\Depense;
use App\Models\Incident;
use App\Models\Versement;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        // Évolution recettes vs dépenses sur les 12 derniers mois.
        $labels = [];
        $recettesParMois = [];
        $depensesParMois = [];

        for ($i = 11; $i >= 0; $i--) {
            $mois = $now->copy()->subMonths($i);
            $debut = $mois->copy()->startOfMonth();
            $fin = $mois->copy()->endOfMonth();

            $labels[] = ucfirst($mois->translatedFormat('M y'));
            $recettesParMois[] = (float) Versement::whereBetween('date_versement', [$debut, $fin])->sum('montant');
            $depensesParMois[] = (float) Depense::whereBetween('date_depense', [$debut, $fin])->sum('montant');
        }

        // Dépenses par catégorie sur l'année en cours.
        $parCategorie = Depense::whereBetween('date_depense', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])
            ->selectRaw('categorie, SUM(montant) as total')
            ->groupBy('categorie')
            ->pluck('total', 'categorie');

        $categoriesLabels = [];
        $categoriesData = [];
        foreach ($parCategorie as $cat => $total) {
            $categoriesLabels[] = Depense::CATEGORIES[$cat] ?? $cat;
            $categoriesData[] = (float) $total;
        }

        $anneeDebut = $now->copy()->startOfYear();
        $anneeFin = $now->copy()->endOfYear();
        $recettesAnnee = (float) Versement::whereBetween('date_versement', [$anneeDebut, $anneeFin])->sum('montant');
        $depensesAnnee = (float) Depense::whereBetween('date_depense', [$anneeDebut, $anneeFin])->sum('montant');

        return view('statistiques.index', [
            'labels' => $labels,
            'recettesParMois' => $recettesParMois,
            'depensesParMois' => $depensesParMois,
            'categoriesLabels' => $categoriesLabels,
            'categoriesData' => $categoriesData,
            'kpis' => [
                'recettes_annee' => $recettesAnnee,
                'depenses_annee' => $depensesAnnee,
                'resultat_annee' => $recettesAnnee - $depensesAnnee,
                'accidents_annee' => Accident::whereBetween('date_accident', [$anneeDebut, $anneeFin])->count(),
                'incidents_annee' => Incident::whereBetween('date_incident', [$anneeDebut, $anneeFin])->count(),
            ],
        ]);
    }
}
