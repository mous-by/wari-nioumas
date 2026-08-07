<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login', [
            'slides' => [
                ['icon' => 'bi-person-badge', 'badge' => 'Chauffeurs', 'title' => 'Gérez vos chauffeurs en toute simplicité', 'text' => 'Fiches complètes, recherche rapide, historique des affectations.'],
                ['icon' => 'bi-truck-front', 'badge' => 'Véhicules', 'title' => 'Suivez l\'état de votre flotte en temps réel', 'text' => 'Immatriculation, statut, entretien : tout au même endroit.'],
                ['icon' => 'bi-cash-coin', 'badge' => 'Recettes', 'title' => 'Recettes et versements centralisés', 'text' => 'Suivi journalier, hebdomadaire, mensuel et annuel automatisé.'],
                ['icon' => 'bi-safe2', 'badge' => 'Caisse', 'title' => 'Caisse et dépenses maîtrisées', 'text' => 'Ouverture, fermeture, entrées et sorties sous contrôle.'],
                ['icon' => 'bi-graph-up-arrow', 'badge' => 'Finances', 'title' => 'Une vision claire de vos finances', 'text' => 'Rapports et tableaux de bord pour piloter votre activité.'],
            ],
            'modules' => [
                ['icon' => 'bi-person-badge', 'label' => 'Chauffeurs'],
                ['icon' => 'bi-truck-front', 'label' => 'Véhicules'],
                ['icon' => 'bi-arrow-left-right', 'label' => 'Affectations'],
                ['icon' => 'bi-cash-coin', 'label' => 'Recettes'],
                ['icon' => 'bi-calendar-x', 'label' => 'Absences'],
                ['icon' => 'bi-receipt', 'label' => 'Dépenses'],
                ['icon' => 'bi-exclamation-triangle', 'label' => 'Accidents'],
                ['icon' => 'bi-flag', 'label' => 'Incidents'],
                ['icon' => 'bi-safe2', 'label' => 'Caisse'],
                ['icon' => 'bi-graph-up-arrow', 'label' => 'Finances'],
                ['icon' => 'bi-key', 'label' => 'Locations'],
                ['icon' => 'bi-speedometer2', 'label' => 'Tableaux de bord'],
            ],
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
