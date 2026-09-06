<?php

namespace App\Support;

use App\Models\Caisse;
use App\Models\MouvementCaisse;
use Illuminate\Database\Eloquent\Model;

/**
 * Alimentation automatique de la caisse à partir d'une source
 * (Versement = entrée, Dépense = sortie).
 *
 * Règle : un mouvement lié n'est créé QUE si une caisse est ouverte au moment
 * où la source est enregistrée. Si la source est ensuite modifiée, le mouvement
 * lié est mis à jour ; si elle est supprimée, le mouvement lié est supprimé.
 */
class CaisseAuto
{
    public static function synchroniser(Model $source, string $type, string $libelle, float $montant, $date, ?int $userId): void
    {
        $mouvement = MouvementCaisse::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->first();

        if ($mouvement) {
            $mouvement->update([
                'type' => $type,
                'libelle' => $libelle,
                'montant' => $montant,
                'date_mouvement' => $date,
            ]);

            return;
        }

        $caisse = Caisse::ouverte()->latest('date_ouverture')->first();

        if (! $caisse) {
            return; // aucune caisse ouverte : on n'alimente pas
        }

        $caisse->mouvements()->create([
            'type' => $type,
            'libelle' => $libelle,
            'montant' => $montant,
            'date_mouvement' => $date,
            'user_id' => $userId,
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
        ]);
    }

    public static function supprimer(Model $source): void
    {
        MouvementCaisse::where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->delete();
    }

    /**
     * Annule un mouvement déjà comptée sans jamais le supprimer ni le
     * modifier : crée une contre-passation (mouvement inverse, même
     * montant, même source) qui pointe vers l'original via reversal_of_id.
     * Idempotent : un mouvement déjà contre-passé renvoie sa contre-passation
     * existante au lieu d'en créer une deuxième.
     */
    public static function contrepasser(MouvementCaisse $original, string $libelle, ?int $userId): MouvementCaisse
    {
        if ($original->reversal) {
            return $original->reversal;
        }

        return $original->caisse->mouvements()->create([
            'type' => $original->type === 'sortie' ? 'entree' : 'sortie',
            'libelle' => $libelle,
            'montant' => $original->montant,
            'date_mouvement' => now(),
            'user_id' => $userId,
            'source_type' => $original->source_type,
            'source_id' => $original->source_id,
            'reversal_of_id' => $original->id,
        ]);
    }
}
