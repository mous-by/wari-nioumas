<?php

namespace App\Support;

use App\Models\Caisse;
use App\Models\User;
use App\Models\Validation;

/**
 * Cœur du workflow de validation par le Directeur général.
 *
 * Le DG (et le superadmin) agit directement ; les autres rôles voient leurs
 * actions sensibles transformées en « demandes de validation » qu'il approuve.
 */
class Approbation
{
    public static function estValideur(?User $user): bool
    {
        return $user && ($user->hasRole('directeur_general') || $user->hasRole('superadmin'));
    }

    /**
     * Crée une demande de validation en attente.
     */
    public static function demander(string $type, string $libelle, array $payload): Validation
    {
        return Validation::create([
            'type' => $type,
            'libelle' => $libelle,
            'demandeur_id' => auth()->id(),
            'payload' => $payload,
            'statut' => 'en_attente',
        ]);
    }

    /**
     * Exécute l'action associée à une demande approuvée.
     */
    public static function executer(Validation $validation): void
    {
        match ($validation->type) {
            'caisse.sortie' => self::executerCaisseSortie($validation),
            default => null,
        };
    }

    private static function executerCaisseSortie(Validation $validation): void
    {
        $p = $validation->payload;
        $caisse = Caisse::find($p['caisse_id'] ?? null);

        if ($caisse && $caisse->estOuverte()) {
            $caisse->mouvements()->create([
                'type' => 'sortie',
                'libelle' => $p['libelle'],
                'montant' => $p['montant'],
                'date_mouvement' => $p['date_mouvement'],
                'user_id' => $validation->demandeur_id,
            ]);
        }
    }
}
