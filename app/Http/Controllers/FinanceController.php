<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\Bulletin;
use App\Models\Depense;
use App\Models\Incident;
use App\Models\Versement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
    /**
     * Un bulletin compte comme charge de salaire dès qu'il est validé
     * (le brouillon ne compte pas).
     */
    private const STATUTS_SALAIRE = ['valide', 'paye'];

    public function index(Request $request): View
    {
        return view('finances.index', $this->rapport($request));
    }

    public function exportPdf(Request $request)
    {
        $pdf = Pdf::loadView('pdf.finances', $this->rapport($request))->setPaper('a4', 'portrait');

        return $pdf->stream('rapport-financier.pdf');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $data = $this->rapport($request);
        $fmt = fn ($m) => number_format((float) $m, 0, ',', ' ');

        $nom = 'rapport-financier-'.$data['debut']->format('Y-m-d').'_'.$data['fin']->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($data, $fmt) {
            $out = fopen('php://output', 'w');
            fprintf($out, "\xEF\xBB\xBF"); // BOM UTF-8 pour Excel

            fputcsv($out, ['Rapport financier WARI NIOUMA'], ';');
            fputcsv($out, ['Période', $data['debut']->format('d/m/Y').' au '.$data['fin']->format('d/m/Y')], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['Mois', 'Recettes', 'Salaires', 'Charges', 'Résultat'], ';');
            foreach ($data['mensuel'] as $ligne) {
                fputcsv($out, [
                    ucfirst($ligne['mois']->translatedFormat('F Y')),
                    $fmt($ligne['recettes']),
                    $fmt($ligne['salaires']),
                    $fmt($ligne['charges']),
                    $fmt($ligne['resultat']),
                ], ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['TOTAL recettes', $fmt($data['recettes'])], ';');
            fputcsv($out, ['TOTAL dépenses', $fmt($data['depenses'])], ';');
            fputcsv($out, ['TOTAL salaires', $fmt($data['salaires'])], ';');
            fputcsv($out, ['TOTAL coût accidents', $fmt($data['coutAccidents'])], ';');
            fputcsv($out, ['TOTAL coût incidents', $fmt($data['coutIncidents'])], ';');
            fputcsv($out, ['TOTAL charges', $fmt($data['charges'])], ';');
            fputcsv($out, ['RÉSULTAT NET', $fmt($data['resultat'])], ';');
            fclose($out);
        }, $nom, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Calcule l'ensemble des données du rapport sur la période demandée.
     */
    private function rapport(Request $request): array
    {
        $debut = $request->filled('debut')
            ? Carbon::parse($request->input('debut'))->startOfDay()
            : Carbon::now()->startOfYear();
        $fin = $request->filled('fin')
            ? Carbon::parse($request->input('fin'))->endOfDay()
            : Carbon::now()->endOfYear();

        if ($fin->lt($debut)) {
            [$debut, $fin] = [$fin->copy()->startOfDay(), $debut->copy()->endOfDay()];
        }

        $recettes = (float) Versement::whereBetween('date_versement', [$debut, $fin])->sum('montant');
        $depenses = (float) Depense::whereBetween('date_depense', [$debut, $fin])->sum('montant');
        $coutAccidents = (float) Accident::whereBetween('date_accident', [$debut, $fin])->sum('cout_reparation');
        $coutIncidents = (float) Incident::whereBetween('date_incident', [$debut, $fin])->sum('cout');
        $salaires = (float) $this->salairesParMois($debut, $fin)->sum();

        $charges = $depenses + $coutAccidents + $coutIncidents + $salaires;
        $resultat = $recettes - $charges;
        $mensuel = $this->recapMensuel($debut, $fin);

        return [
            'debut' => $debut,
            'fin' => $fin,
            'recettes' => $recettes,
            'depenses' => $depenses,
            'coutAccidents' => $coutAccidents,
            'coutIncidents' => $coutIncidents,
            'salaires' => $salaires,
            'charges' => $charges,
            'resultat' => $resultat,
            'mensuel' => $mensuel,
            'stats' => $this->statistiques($recettes, $charges, $resultat, $salaires, $mensuel, $debut, $fin),
            'graphique' => $this->donneesGraphique($depenses, $coutAccidents, $coutIncidents, $salaires, $mensuel),
        ];
    }

    /**
     * Récapitulatif mois par mois (recettes / salaires / charges / résultat) sur la période.
     */
    private function recapMensuel(Carbon $debut, Carbon $fin): array
    {
        $lignes = [];
        $curseur = $debut->copy()->startOfMonth();
        $borne = $fin->copy()->endOfMonth();
        $iterations = 0;
        $salairesParMois = $this->salairesParMois($debut, $fin);

        while ($curseur->lte($borne) && $iterations < 24) {
            $moisDebut = $curseur->copy()->startOfMonth();
            $moisFin = $curseur->copy()->endOfMonth();

            $recettes = (float) Versement::whereBetween('date_versement', [$moisDebut, $moisFin])->sum('montant');
            $depenses = (float) Depense::whereBetween('date_depense', [$moisDebut, $moisFin])->sum('montant');
            $coutAcc = (float) Accident::whereBetween('date_accident', [$moisDebut, $moisFin])->sum('cout_reparation');
            $coutInc = (float) Incident::whereBetween('date_incident', [$moisDebut, $moisFin])->sum('cout');
            $salaires = (float) ($salairesParMois[(int) $curseur->format('Ym')] ?? 0);
            $charges = $depenses + $coutAcc + $coutInc + $salaires;

            $lignes[] = [
                'mois' => $curseur->copy(),
                'recettes' => $recettes,
                'salaires' => $salaires,
                'charges' => $charges,
                'resultat' => $recettes - $charges,
            ];

            $curseur->addMonth();
            $iterations++;
        }

        return $lignes;
    }

    /**
     * Bulletins de salaire de la période. La période d'un bulletin est son mois de paie
     * (periode_annee / periode_mois), pas sa date de saisie : on compare donc des mois entiers.
     */
    private function bulletinsSalaire(Carbon $debut, Carbon $fin): Builder
    {
        return Bulletin::query()
            ->whereIn('statut', self::STATUTS_SALAIRE)
            ->whereRaw('periode_annee * 100 + periode_mois BETWEEN ? AND ?', [
                (int) $debut->format('Ym'),
                (int) $fin->format('Ym'),
            ]);
    }

    /**
     * Net à payer des salaires par mois de paie, indexé par AAAAMM (ex. 202610 => montant).
     */
    private function salairesParMois(Carbon $debut, Carbon $fin): Collection
    {
        return $this->bulletinsSalaire($debut, $fin)
            ->selectRaw('periode_annee, periode_mois, SUM(net_a_payer) AS total')
            ->groupBy('periode_annee', 'periode_mois')
            ->get()
            ->mapWithKeys(fn ($ligne) => [
                (int) sprintf('%04d%02d', $ligne->periode_annee, $ligne->periode_mois) => (float) $ligne->total,
            ]);
    }

    /**
     * Indicateurs de lecture rapide : moyennes par mois, rentabilité, poids des salaires.
     * Les mois « vides » (aucune recette ni charge) ne comptent pas pour les mois extrêmes.
     */
    private function statistiques(float $recettes, float $charges, float $resultat, float $salaires, array $mensuel, Carbon $debut, Carbon $fin): array
    {
        $nbMois = max(count($mensuel), 1);
        $moisActifs = collect($mensuel)->filter(fn ($ligne) => $ligne['recettes'] > 0 || $ligne['charges'] > 0);

        return [
            'moyenne_recettes' => $recettes / $nbMois,
            'moyenne_charges' => $charges / $nbMois,
            'taux_marge' => $recettes > 0 ? $resultat / $recettes * 100 : null,
            'part_salaires' => $charges > 0 ? $salaires / $charges * 100 : null,
            'nb_salaries' => $this->bulletinsSalaire($debut, $fin)->distinct('personnel_id')->count('personnel_id'),
            'mois_rentable' => $moisActifs->sortByDesc('resultat')->first(),
            'mois_charge' => $moisActifs->sortByDesc('charges')->first(),
        ];
    }

    /**
     * Données des graphiques : recettes et charges par mois, répartition des charges.
     */
    private function donneesGraphique(float $depenses, float $coutAccidents, float $coutIncidents, float $salaires, array $mensuel): array
    {
        $repartition = array_filter([
            'Dépenses du parc' => $depenses,
            'Salaires' => $salaires,
            'Accidents' => $coutAccidents,
            'Incidents' => $coutIncidents,
        ], fn ($montant) => $montant > 0);

        return [
            'labels' => collect($mensuel)->map(fn ($ligne) => ucfirst($ligne['mois']->translatedFormat('M Y')))->values()->all(),
            'recettes' => collect($mensuel)->pluck('recettes')->values()->all(),
            'charges' => collect($mensuel)->pluck('charges')->values()->all(),
            'repartitionLabels' => array_keys($repartition),
            'repartitionValeurs' => array_values($repartition),
        ];
    }
}
