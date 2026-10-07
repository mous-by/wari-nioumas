<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermuterAffectationRequest;
use App\Http\Requests\StoreAffectationRequest;
use App\Http\Requests\StoreVoyageRequest;
use App\Http\Requests\UpdateAffectationRequest;
use App\Models\Affectation;
use App\Models\Chauffeur;
use App\Models\Vehicule;
use App\Models\Versement;
use App\Models\Voyage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AffectationController extends Controller
{
    public function index(): View
    {
        return view('affectations.index', [
            'affectations' => Affectation::with(['vehicule', 'chauffeur'])->latest('date_debut')->get(),
            'vehicules' => Vehicule::orderBy('immatriculation')->get(),
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
            'affectationsActives' => Affectation::whereNull('date_fin')->with(['chauffeur', 'vehicule'])->get(),
        ]);
    }

    public function store(StoreAffectationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Un double-clic sur « Enregistrer » soumet deux fois le même
        // formulaire : sans ce garde-fou, la 2e soumission referme aussitôt
        // l'affectation tout juste créée par la 1re (même véhicule +
        // chauffeur déjà actifs) et en recrée une identique, laissant une
        // ligne fantôme "ouverte puis fermée le même jour" dans l'historique.
        $dejaActive = Affectation::where('vehicule_id', $data['vehicule_id'])
            ->where('chauffeur_id', $data['chauffeur_id'])
            ->whereNull('date_fin')
            ->exists();

        if ($dejaActive) {
            return redirect()->route('affectations.index')->with('status', 'Cette affectation est déjà active.');
        }

        DB::transaction(function () use ($data) {
            Affectation::where('vehicule_id', $data['vehicule_id'])
                ->whereNull('date_fin')
                ->update(['date_fin' => $data['date_debut'], 'motif_fin' => 'Réaffecté à un autre chauffeur']);

            Affectation::where('chauffeur_id', $data['chauffeur_id'])
                ->whereNull('date_fin')
                ->update(['date_fin' => $data['date_debut'], 'motif_fin' => 'Affecté à un autre véhicule']);

            Affectation::create([...$data, 'user_id' => auth()->id()]);
        });

        $avertissement = $this->avertissementDocuments(
            Vehicule::find($data['vehicule_id']),
            Chauffeur::find($data['chauffeur_id']),
        );

        $redirect = redirect()->route('affectations.index')->with('status', 'Affectation enregistrée avec succès.');

        return $avertissement ? $redirect->with('avertissement', $avertissement) : $redirect;
    }

    /**
     * Avertissement non bloquant quand le véhicule ou le chauffeur affecté a
     * au moins un document expiré (carte grise, assurance, vignette, permis).
     * Ne bloque pas l'affectation : alerte la direction pour qu'elle vérifie.
     */
    private function avertissementDocuments(?Vehicule $vehicule, ?Chauffeur $chauffeur): ?string
    {
        $messages = [];

        if ($vehicule && $vehicule->documents()->expires()->exists()) {
            $messages[] = "le véhicule {$vehicule->immatriculation} possède un document expiré";
        }
        if ($chauffeur && $chauffeur->documents()->expires()->exists()) {
            $messages[] = "le chauffeur {$chauffeur->nom_complet} possède un document expiré (permis ou autre)";
        }

        if (! $messages) {
            return null;
        }

        return 'Attention : '.implode(' et ', $messages).'. Vérification nécessaire avant son utilisation.';
    }

    /**
     * Permute les véhicules de deux chauffeurs : chacun garde ou change son
     * montant/périodicité au choix (saisi dans le formulaire, pré-rempli avec
     * sa valeur actuelle) — ce n'est pas automatique dans un sens ou l'autre.
     * Réutilise le même mécanisme que store() (fermeture de l'affectation en
     * cours + création d'une nouvelle), appliqué aux deux chauffeurs dans une
     * seule transaction atomique.
     */
    public function permuter(PermuterAffectationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $affectation1 = Affectation::where('chauffeur_id', $data['chauffeur_1_id'])->whereNull('date_fin')->firstOrFail();
        $affectation2 = Affectation::where('chauffeur_id', $data['chauffeur_2_id'])->whereNull('date_fin')->firstOrFail();

        // Capturés avant la transaction : utilisés aussi après coup pour
        // l'avertissement sur les documents, une fois les véhicules permutés.
        $vehicule1Id = $affectation1->vehicule_id;
        $vehicule2Id = $affectation2->vehicule_id;

        DB::transaction(function () use ($data, $affectation1, $affectation2, $vehicule1Id, $vehicule2Id) {
            $vehicule1 = $vehicule1Id;
            $vehicule2 = $vehicule2Id;
            $chauffeur1 = $affectation1->chauffeur;
            $chauffeur2 = $affectation2->chauffeur;

            $affectation1->update([
                'date_fin' => $data['date_permutation'],
                'motif_fin' => 'Permutation avec '.($chauffeur2?->nom_complet ?? 'un autre chauffeur'),
            ]);
            $affectation2->update([
                'date_fin' => $data['date_permutation'],
                'motif_fin' => 'Permutation avec '.($chauffeur1?->nom_complet ?? 'un autre chauffeur'),
            ]);

            Affectation::create([
                'vehicule_id' => $vehicule2,
                'chauffeur_id' => $data['chauffeur_1_id'],
                'montant_journalier' => $data['montant_1'] ?? null,
                'periodicite' => $data['periodicite_1'],
                'date_debut' => $data['date_permutation'],
                'observations' => $data['observations'] ?? null,
                'user_id' => auth()->id(),
            ]);
            Affectation::create([
                'vehicule_id' => $vehicule1,
                'chauffeur_id' => $data['chauffeur_2_id'],
                'montant_journalier' => $data['montant_2'] ?? null,
                'periodicite' => $data['periodicite_2'],
                'date_debut' => $data['date_permutation'],
                'observations' => $data['observations'] ?? null,
                'user_id' => auth()->id(),
            ]);
        });

        $avertissement1 = $this->avertissementDocuments(Vehicule::find($vehicule2Id), Chauffeur::find($data['chauffeur_1_id']));
        $avertissement2 = $this->avertissementDocuments(Vehicule::find($vehicule1Id), Chauffeur::find($data['chauffeur_2_id']));
        $avertissement = collect([$avertissement1, $avertissement2])->filter()->implode(' ');

        $redirect = redirect()->route('affectations.index')->with('status', 'Permutation effectuée avec succès.');

        return $avertissement ? $redirect->with('avertissement', $avertissement) : $redirect;
    }

    public function update(UpdateAffectationRequest $request, Affectation $affectation): RedirectResponse
    {
        $affectation->update($request->validated());

        return redirect()->route('affectations.index')->with('status', 'Affectation mise à jour avec succès.');
    }

    public function terminer(Affectation $affectation): RedirectResponse
    {
        $affectation->update([
            'date_fin' => now(),
            'motif_fin' => 'Terminée manuellement',
        ]);

        return back()->with('status', 'Affectation terminée avec succès.');
    }

    public function destroy(Affectation $affectation): RedirectResponse
    {
        $affectation->delete();

        return back()->with('status', 'Affectation supprimée avec succès.');
    }

    /**
     * Ajoute un voyage (date + montant) à une affectation « voyage ». Son
     * montant s'accumule automatiquement au total du chauffeur, ET crée un
     * versement classique (chauffeur + date + montant) pour que l'argent
     * entre en Caisse et apparaisse dans l'historique des versements de la
     * page Recettes, comme un versement journalier/mensuel.
     */
    public function ajouterVoyage(StoreVoyageRequest $request, Affectation $affectation): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $affectation) {
            Voyage::create([
                ...$data,
                'affectation_id' => $affectation->id,
                'user_id' => auth()->id(),
            ]);

            Versement::create([
                'chauffeur_id' => $affectation->chauffeur_id,
                'date_versement' => $data['date_voyage'],
                'montant' => $data['montant'],
                'observations' => trim('Voyage — '.($affectation->vehicule?->immatriculation ?? '').' '.($data['observations'] ?? '')),
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('affectations.index')->with('status', 'Voyage enregistré avec succès.');
    }
}
