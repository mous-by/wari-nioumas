<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Les champs monétaires (classe .champ-montant) s'affichent avec des espaces
 * ("3 200 000"). Avant validation, on retire ces espaces (normaux, insécables
 * ou fines insécables, selon le navigateur) et on remplace la virgule par un
 * point, pour que la règle `numeric` reçoive "3200000".
 *
 * Tout nouveau champ .champ-montant doit figurer dans CHAMPS (un test le vérifie).
 */
class NormaliserMontants
{
    public const CHAMPS = [
        'montant', 'montant_1', 'montant_2', 'montant_total', 'montant_paye',
        'montant_journalier', 'montant_demande', 'montant_accorde',
        'solde_ouverture', 'cout', 'cout_reparation', 'salaire_base',
        'primes', 'retenues',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $request->merge($this->normaliser($request->all()));

        return $next($request);
    }

    private function normaliser(array $donnees): array
    {
        foreach ($donnees as $cle => $valeur) {
            if (is_array($valeur)) {
                $donnees[$cle] = $this->normaliser($valeur);
            } elseif (is_string($valeur) && in_array($cle, self::CHAMPS, true)) {
                $donnees[$cle] = str_replace(',', '.', preg_replace('/[\s\x{00A0}\x{202F}]+/u', '', $valeur));
            }
        }

        return $donnees;
    }
}
