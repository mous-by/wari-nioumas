<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Chauffeur;
use App\Models\Document;
use App\Models\Vehicule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Page de surveillance globale : tous les documents de véhicules et de
     * chauffeurs, filtrables par statut d'échéance.
     */
    public function index(): View
    {
        $documents = Document::with('documentable')->surveilles()->get()
            ->sortBy('date_expiration')
            ->values();

        $parStatut = $documents->groupBy('statut');

        return view('documents.index', [
            'documents' => $documents,
            'stats' => [
                'expire' => $parStatut->get('expire', collect())->count(),
                'proche' => $parStatut->get('proche', collect())->count(),
                'attention' => $parStatut->get('attention', collect())->count(),
                'valide' => $parStatut->get('valide', collect())->count(),
            ],
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $documentable = $this->resoudreProprietaire($data['documentable_type'], $data['documentable_id']);

        if ($data['type_document'] === 'permis_conduite' && $documentable instanceof Chauffeur) {
            // Le permis suit un chemin dédié qui tient aussi à jour les champs
            // permis_numero / permis_date_validite du chauffeur (voir Document).
            $documentable->permis_numero = $data['numero'] ?? $documentable->permis_numero;
            $documentable->permis_date_validite = $data['date_expiration'] ?? $documentable->permis_date_validite;
            $documentable->save();

            $document = Document::enregistrerPermis($documentable, auth()->id());
            $document->update([
                'date_etablissement' => $data['date_etablissement'] ?? null,
                'observations' => $data['observations'] ?? null,
            ]);
        } else {
            $document = new Document([
                'documentable_type' => $documentable->getMorphClass(),
                'documentable_id' => $documentable->getKey(),
                'type_document' => $data['type_document'],
                'libelle_autre' => $data['libelle_autre'] ?? null,
                'numero' => $data['numero'] ?? null,
                'date_etablissement' => $data['date_etablissement'] ?? null,
                'date_expiration' => $data['date_expiration'] ?? null,
                'observations' => $data['observations'] ?? null,
                'created_by' => auth()->id(),
            ]);
            $document->save();
        }

        $this->enregistrerFichier($request, $document);

        return back()->with('status', 'Document ajouté avec succès.');
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $data = $request->validated();
        $ancienneEcheance = $document->date_expiration;

        $document->fill([
            'type_document' => $data['type_document'],
            'libelle_autre' => $data['libelle_autre'] ?? null,
            'numero' => $data['numero'] ?? null,
            'date_etablissement' => $data['date_etablissement'] ?? null,
            'date_expiration' => $data['date_expiration'] ?? null,
            'observations' => $data['observations'] ?? null,
            'updated_by' => auth()->id(),
        ]);
        $document->save();

        if ($ancienneEcheance?->toDateString() !== $document->date_expiration?->toDateString()) {
            $document->historiques()->create([
                'ancienne_echeance' => $ancienneEcheance,
                'nouvelle_echeance' => $document->date_expiration,
                'user_id' => auth()->id(),
            ]);
        }

        $this->enregistrerFichier($request, $document);

        // Le permis modifié depuis la fiche document reste visible tel quel
        // sur la fiche chauffeur (numéro, validité).
        if ($document->type_document === 'permis_conduite' && $document->documentable instanceof Chauffeur) {
            $document->documentable->forceFill([
                'permis_numero' => $document->numero,
                'permis_date_validite' => $document->date_expiration,
            ])->saveQuietly();
        }

        return back()->with('status', 'Document mis à jour avec succès.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        if ($document->fichier) {
            Storage::disk('public')->delete($document->fichier);
        }

        $document->delete();

        return back()->with('status', 'Document supprimé avec succès.');
    }

    private function resoudreProprietaire(string $type, int $id): Vehicule|Chauffeur
    {
        return $type === 'vehicule' ? Vehicule::findOrFail($id) : Chauffeur::findOrFail($id);
    }

    private function enregistrerFichier(StoreDocumentRequest|UpdateDocumentRequest $request, Document $document): void
    {
        if (! $request->hasFile('fichier')) {
            return;
        }

        if ($document->fichier) {
            Storage::disk('public')->delete($document->fichier);
        }

        $dossier = ($document->documentable_type === Vehicule::class ? 'vehicules' : 'chauffeurs').'/'.$document->documentable_id;
        $chemin = $request->file('fichier')->store($dossier, 'public');

        $document->update([
            'fichier' => $chemin,
            'nom_original' => $request->file('fichier')->getClientOriginalName(),
        ]);
    }
}
