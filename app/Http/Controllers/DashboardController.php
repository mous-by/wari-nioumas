<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\Chauffeur;
use App\Models\Depense;
use App\Models\Incident;
use App\Models\Personnel;
use App\Models\User;
use App\Models\Vehicule;
use App\Models\Versement;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();

        return view('home', [
            'usersCount' => User::where('actif', true)
                ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'superadmin'))
                ->count(),
            'chauffeursCount' => Chauffeur::where('statut', 'actif')->count(),
            'vehiculesCount' => Vehicule::where('etat', 'actif')->count(),
            'recettesDuMois' => (float) Versement::whereBetween('date_versement', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('montant'),
            'depensesDuMois' => (float) Depense::whereBetween('date_depense', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])->sum('montant'),
            'accidentsAnnee' => Accident::whereBetween('date_accident', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->count(),
            'incidentsAnnee' => Incident::whereBetween('date_incident', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])->count(),
            'masseSalariale' => (float) Personnel::where('statut', 'actif')->sum('salaire_base'),
            // Tableaux de filtrage rapide (10 derniers enregistrements)
            'derniersVersements' => Versement::with('chauffeur')->latest('date_versement')->latest('id')->limit(10)->get(),
            'dernieresDepenses' => Depense::with('vehicule')->latest('date_depense')->latest('id')->limit(10)->get(),
            'derniersSinistres' => Accident::with('vehicule')->latest('date_accident')->latest('id')->limit(10)->get(),
        ]);
    }
}
