<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\Depense;
use App\Models\Incident;
use App\Models\Versement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceController extends Controller
{
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
            fputcsv($out, ['Mois', 'Recettes', 'Charges', 'Résultat'], ';');
            foreach ($data['mensuel'] as $ligne) {
                fputcsv($out, [
                    ucfirst($ligne['mois']->translatedFormat('F Y')),
                    $fmt($ligne['recettes']),
                    $fmt($ligne['charges']),
                    $fmt($ligne['resultat']),
                ], ';');
            }
            fputcsv($out, [], ';');
            fputcsv($out, ['TOTAL recettes', $fmt($data['recettes'])], ';');
            fputcsv($out, ['TOTAL dépenses', $fmt($data['depenses'])], ';');
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

        $charges = $depenses + $coutAccidents + $coutIncidents;

        return [
            'debut' => $debut,
            'fin' => $fin,
            'recettes' => $recettes,
            'depenses' => $depenses,
            'coutAccidents' => $coutAccidents,
            'coutIncidents' => $coutIncidents,
            'charges' => $charges,
            'resultat' => $recettes - $charges,
            'mensuel' => $this->recapMensuel($debut, $fin),
        ];
    }

    /**
     * Récapitulatif mois par mois (recettes / dépenses / résultat) sur la période.
     */
    private function recapMensuel(Carbon $debut, Carbon $fin): array
    {
        $lignes = [];
        $curseur = $debut->copy()->startOfMonth();
        $borne = $fin->copy()->endOfMonth();
        $iterations = 0;

        while ($curseur->lte($borne) && $iterations < 24) {
            $moisDebut = $curseur->copy()->startOfMonth();
            $moisFin = $curseur->copy()->endOfMonth();

            $recettes = (float) Versement::whereBetween('date_versement', [$moisDebut, $moisFin])->sum('montant');
            $depenses = (float) Depense::whereBetween('date_depense', [$moisDebut, $moisFin])->sum('montant');
            $coutAcc = (float) Accident::whereBetween('date_accident', [$moisDebut, $moisFin])->sum('cout_reparation');
            $coutInc = (float) Incident::whereBetween('date_incident', [$moisDebut, $moisFin])->sum('cout');
            $charges = $depenses + $coutAcc + $coutInc;

            $lignes[] = [
                'mois' => $curseur->copy(),
                'recettes' => $recettes,
                'charges' => $charges,
                'resultat' => $recettes - $charges,
            ];

            $curseur->addMonth();
            $iterations++;
        }

        return $lignes;
    }
}
