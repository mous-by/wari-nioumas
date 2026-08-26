<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttestationVenteRequest;
use App\Http\Requests\UpdateAttestationVenteRequest;
use App\Models\AttestationVente;
use App\Models\Vehicule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttestationVenteController extends Controller
{
    public function index(): View
    {
        return view('configuration.attestations.index', [
            'attestations' => AttestationVente::with('user')->latest('date_vente')->latest('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('configuration.attestations.create', [
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
        ]);
    }

    public function store(StoreAttestationVenteRequest $request): RedirectResponse
    {
        $attestation = AttestationVente::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('attestations.show', $attestation)->with('status', 'Attestation de vente créée avec succès.');
    }

    public function show(AttestationVente $attestation): View
    {
        return view('configuration.attestations.show', ['attestation' => $attestation]);
    }

    public function edit(AttestationVente $attestation): View|RedirectResponse
    {
        if ($attestation->statut !== 'brouillon') {
            return redirect()->route('attestations.show', $attestation)->withErrors(['attestation' => 'Cette attestation est validée, elle ne peut plus être modifiée.']);
        }

        return view('configuration.attestations.edit', [
            'attestation' => $attestation,
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
        ]);
    }

    public function update(UpdateAttestationVenteRequest $request, AttestationVente $attestation): RedirectResponse
    {
        if ($attestation->statut !== 'brouillon') {
            return redirect()->route('attestations.show', $attestation)->withErrors(['attestation' => 'Cette attestation est validée, elle ne peut plus être modifiée.']);
        }

        $attestation->update($request->validated());

        return redirect()->route('attestations.show', $attestation)->with('status', 'Attestation de vente mise à jour avec succès.');
    }

    public function valider(AttestationVente $attestation): RedirectResponse
    {
        if ($attestation->statut !== 'brouillon') {
            return back()->withErrors(['attestation' => 'Cette attestation est déjà validée.']);
        }

        DB::transaction(function () use ($attestation) {
            $attestation->update(['statut' => 'validee']);

            // Si l'attestation concerne un véhicule déjà enregistré, on le
            // marque "vendu" — seulement maintenant (pas dès le brouillon,
            // qui reste modifiable/supprimable).
            $vehicule = $attestation->vehicule;

            if ($vehicule && $vehicule->etat !== 'vendu') {
                $ancienEtat = $vehicule->etat;
                $vehicule->update(['etat' => 'vendu']);
                $vehicule->etatHistoriques()->create([
                    'ancien_etat' => $ancienEtat,
                    'nouveau_etat' => 'vendu',
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return back()->with('status', 'Attestation validée avec succès.');
    }

    public function destroy(AttestationVente $attestation): RedirectResponse
    {
        $attestation->delete();

        return redirect()->route('attestations.index')->with('status', 'Attestation supprimée avec succès.');
    }

    public function pdf(AttestationVente $attestation)
    {
        $pdf = Pdf::loadView('pdf.attestation-vente', ['attestation' => $attestation])->setPaper('a4', 'portrait');

        return $pdf->stream('attestation-'.$attestation->numero.'.pdf');
    }
}
