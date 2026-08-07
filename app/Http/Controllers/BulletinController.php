<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBulletinRequest;
use App\Http\Requests\UpdateBulletinRequest;
use App\Models\Bulletin;
use App\Models\Personnel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BulletinController extends Controller
{
    public function index(Request $request): View
    {
        $mois = (int) $request->input('mois', now()->format('n'));
        $annee = (int) $request->input('annee', now()->format('Y'));

        $bulletins = Bulletin::with('personnel')
            ->where('periode_mois', $mois)
            ->where('periode_annee', $annee)
            ->get();

        return view('bulletins.index', [
            'bulletins' => $bulletins,
            'mois' => $mois,
            'annee' => $annee,
            'personnels' => Personnel::where('statut', 'actif')->orderBy('nom')->get(),
            'totalNet' => (float) $bulletins->sum('net_a_payer'),
        ]);
    }

    public function store(StoreBulletinRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $personnel = Personnel::findOrFail($data['personnel_id']);

        $existe = Bulletin::where('personnel_id', $personnel->id)
            ->where('periode_mois', $data['periode_mois'])
            ->where('periode_annee', $data['periode_annee'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['personnel_id' => 'Un bulletin existe déjà pour cet employé sur cette période.'])->withInput();
        }

        Bulletin::create([
            'personnel_id' => $personnel->id,
            'periode_mois' => $data['periode_mois'],
            'periode_annee' => $data['periode_annee'],
            'salaire_base' => $personnel->salaire_base,
            'primes' => $data['primes'] ?? 0,
            'retenues' => $data['retenues'] ?? 0,
            'observations' => $data['observations'] ?? null,
            'statut' => 'brouillon',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('bulletins.index', ['mois' => $data['periode_mois'], 'annee' => $data['periode_annee']])
            ->with('status', 'Bulletin généré avec succès.');
    }

    /**
     * Génère en une fois les bulletins de tous les employés actifs qui n'en ont
     * pas encore pour la période choisie.
     */
    public function genererMois(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'periode_mois' => ['required', 'integer', 'between:1,12'],
            'periode_annee' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $mois = (int) $validated['periode_mois'];
        $annee = (int) $validated['periode_annee'];

        $deja = Bulletin::where('periode_mois', $mois)->where('periode_annee', $annee)->pluck('personnel_id')->all();

        $count = 0;
        DB::transaction(function () use ($mois, $annee, $deja, &$count) {
            foreach (Personnel::where('statut', 'actif')->whereNotIn('id', $deja)->get() as $personnel) {
                Bulletin::create([
                    'personnel_id' => $personnel->id,
                    'periode_mois' => $mois,
                    'periode_annee' => $annee,
                    'salaire_base' => $personnel->salaire_base,
                    'primes' => 0,
                    'retenues' => 0,
                    'statut' => 'brouillon',
                    'user_id' => auth()->id(),
                ]);
                $count++;
            }
        });

        return redirect()->route('bulletins.index', ['mois' => $mois, 'annee' => $annee])
            ->with('status', $count > 0 ? "$count bulletin(s) généré(s)." : 'Tous les employés actifs ont déjà un bulletin pour cette période.');
    }

    public function update(UpdateBulletinRequest $request, Bulletin $bulletin): RedirectResponse
    {
        $bulletin->update($request->validated());

        return redirect()->route('bulletins.index', ['mois' => $bulletin->periode_mois, 'annee' => $bulletin->periode_annee])
            ->with('status', 'Bulletin mis à jour avec succès.');
    }

    public function destroy(Bulletin $bulletin): RedirectResponse
    {
        $bulletin->delete();

        return back()->with('status', 'Bulletin supprimé avec succès.');
    }

    public function pdf(Bulletin $bulletin)
    {
        $bulletin->load('personnel');

        $pdf = Pdf::loadView('pdf.bulletin', ['bulletin' => $bulletin]);

        return $pdf->stream('bulletin-'.$bulletin->personnel->matricule.'-'.$bulletin->periode_annee.'-'.$bulletin->periode_mois.'.pdf');
    }
}
