<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCasSocialDocumentRequest;
use App\Http\Requests\StoreCasSocialRequest;
use App\Http\Requests\UpdateCasSocialRequest;
use App\Models\CasSocial;
use App\Models\CasSocialDocument;
use App\Models\Personnel;
use App\Models\TypeCasSocial;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CasSocialController extends Controller
{
    public function index(Request $request): View
    {
        $query = CasSocial::with(['personnel', 'type']);

        if ($numero = $request->query('numero')) {
            $query->where('numero', 'like', "%{$numero}%");
        }
        if ($personnelId = $request->query('personnel_id')) {
            $query->where('personnel_id', $personnelId);
        }
        if ($typeId = $request->query('type_cas_social_id')) {
            $query->where('type_cas_social_id', $typeId);
        }
        if ($statut = $request->query('statut')) {
            $query->where('statut', $statut);
        }
        if ($debut = $request->query('debut')) {
            $query->whereDate('date_cas', '>=', $debut);
        }
        if ($fin = $request->query('fin')) {
            $query->whereDate('date_cas', '<=', $fin);
        }

        $casSociaux = $query->latest('date_cas')->latest('id')->get();

        $now = Carbon::now();
        $tousLesCas = CasSocial::query();

        return view('cas_sociaux.index', [
            'casSociaux' => $casSociaux,
            'types' => TypeCasSocial::orderBy('libelle')->get(),
            'personnels' => Personnel::where('statut', 'actif')->orderBy('nom')->get(),
            'stats' => [
                'total' => (clone $tousLesCas)->count(),
                'ceMois' => (clone $tousLesCas)->whereBetween('date_cas', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->count(),
                'montantDemande' => (float) (clone $tousLesCas)->sum('montant_demande'),
                'montantAccorde' => (float) (clone $tousLesCas)->sum('montant_accorde'),
                'montantPaye' => (float) (clone $tousLesCas)->where('statut', 'paye')->sum('montant_accorde'),
                'montantEnAttente' => (float) (clone $tousLesCas)->whereIn('statut', ['en_attente', 'approuve'])->sum('montant_accorde'),
                'beneficiaires' => (clone $tousLesCas)->distinct('personnel_id')->count('personnel_id'),
                'parType' => (clone $tousLesCas)->with('type')->get()->groupBy(fn (CasSocial $c) => $c->type?->libelle ?? '—')->map->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('cas_sociaux.create', [
            'types' => TypeCasSocial::actif()->orderBy('libelle')->get(),
            'personnels' => Personnel::where('statut', 'actif')->orderBy('nom')->get(),
        ]);
    }

    public function store(StoreCasSocialRequest $request): RedirectResponse
    {
        $casSocial = CasSocial::create($request->validated());

        return redirect()->route('cas-sociaux.show', $casSocial)->with('status', 'Cas social créé avec succès.');
    }

    public function show(CasSocial $casSocial): View
    {
        $casSocial->load(['personnel', 'type', 'demandeur', 'valideur', 'payeur', 'annulePar', 'documents', 'mouvements.caisse']);

        return view('cas_sociaux.show', ['casSocial' => $casSocial]);
    }

    public function edit(CasSocial $casSocial): View|RedirectResponse
    {
        if (! $casSocial->estModifiable()) {
            return redirect()->route('cas-sociaux.show', $casSocial)->withErrors(['statut' => 'Ce cas social ne peut plus être modifié.']);
        }

        return view('cas_sociaux.edit', [
            'casSocial' => $casSocial,
            'types' => TypeCasSocial::actif()->orderBy('libelle')->get(),
            'personnels' => Personnel::where('statut', 'actif')->orderBy('nom')->get(),
        ]);
    }

    public function update(UpdateCasSocialRequest $request, CasSocial $casSocial): RedirectResponse
    {
        if (! $casSocial->estModifiable()) {
            return redirect()->route('cas-sociaux.show', $casSocial)->withErrors(['statut' => 'Ce cas social ne peut plus être modifié.']);
        }

        $casSocial->update($request->validated());

        return redirect()->route('cas-sociaux.show', $casSocial)->with('status', 'Cas social mis à jour avec succès.');
    }

    public function soumettre(CasSocial $casSocial): RedirectResponse
    {
        return $this->transition(fn () => $casSocial->soumettre(), $casSocial, 'Cas social soumis pour approbation.');
    }

    public function approuver(Request $request, CasSocial $casSocial): RedirectResponse
    {
        $validated = $request->validate([
            'montant_accorde' => ['required', 'numeric', 'min:0'],
        ]);

        return $this->transition(
            fn () => $casSocial->approuver($request->user(), (float) $validated['montant_accorde']),
            $casSocial,
            'Cas social approuvé avec succès.'
        );
    }

    public function rejeter(Request $request, CasSocial $casSocial): RedirectResponse
    {
        $validated = $request->validate(['motif' => ['required', 'string']]);

        return $this->transition(
            fn () => $casSocial->rejeter($request->user(), $validated['motif']),
            $casSocial,
            'Cas social rejeté.'
        );
    }

    public function reprendre(CasSocial $casSocial): RedirectResponse
    {
        return $this->transition(fn () => $casSocial->reprendre(), $casSocial, 'Cas social remis en brouillon.');
    }

    public function payer(Request $request, CasSocial $casSocial): RedirectResponse
    {
        return $this->transition(
            fn () => $casSocial->payer($request->user()),
            $casSocial,
            'Paiement enregistré : la sortie de caisse a été créée avec succès.'
        );
    }

    public function annuler(Request $request, CasSocial $casSocial): RedirectResponse
    {
        // Annuler un cas déjà payé retire de l'argent virtuellement de la
        // caisse (contre-passation) : exige donc explicitement le droit de
        // payer EN PLUS du droit d'approuver, pas seulement l'un des deux.
        if ($casSocial->statut === 'paye' && ! $request->user()->can('cas_sociaux.payer')) {
            return back()->withErrors(['statut' => "Seul un utilisateur pouvant effectuer un paiement peut annuler un cas déjà payé."]);
        }

        $validated = $request->validate(['motif' => ['required', 'string']]);

        return $this->transition(
            fn () => $casSocial->annuler($request->user(), $validated['motif']),
            $casSocial,
            'Cas social annulé avec succès.'
        );
    }

    public function storeDocument(StoreCasSocialDocumentRequest $request, CasSocial $casSocial): RedirectResponse
    {
        $chemin = $request->file('fichier')->store('cas-sociaux/'.$casSocial->id, 'public');

        CasSocialDocument::create([
            'cas_social_id' => $casSocial->id,
            'type_document' => $request->validated('type_document'),
            'chemin' => $chemin,
            'nom_original' => $request->file('fichier')->getClientOriginalName(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('status', 'Document ajouté avec succès.');
    }

    public function destroyDocument(CasSocialDocument $document): RedirectResponse
    {
        Storage::disk('public')->delete($document->chemin);
        $casSocial = $document->casSocial;
        $document->delete();

        return redirect()->route('cas-sociaux.show', $casSocial)->with('status', 'Document supprimé avec succès.');
    }

    public function pdf(CasSocial $casSocial)
    {
        $casSocial->load(['personnel', 'type', 'valideur', 'payeur', 'mouvements']);

        $pdf = Pdf::loadView('pdf.cas-social', ['casSocial' => $casSocial])->setPaper('a4', 'portrait');

        return $pdf->stream('cas-social-'.$casSocial->numero.'.pdf');
    }

    private function transition(\Closure $action, CasSocial $casSocial, string $message): RedirectResponse
    {
        try {
            $action();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['statut' => $e->getMessage()]);
        }

        return redirect()->route('cas-sociaux.show', $casSocial)->with('status', $message);
    }
}
