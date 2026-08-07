<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Models\Chauffeur;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function index(): View
    {
        $personnels = Personnel::with(['user', 'chauffeur'])->orderBy('nom')->get();

        return view('personnel.index', [
            'personnels' => $personnels,
            'users' => User::orderBy('name')->get(),
            'chauffeurs' => Chauffeur::orderBy('nom')->get(),
            'masseSalariale' => (float) $personnels->where('statut', 'actif')->sum('salaire_base'),
            'effectif' => $personnels->where('statut', 'actif')->count(),
        ]);
    }

    public function store(StorePersonnelRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $personnel = Personnel::create($request->validated());

            $personnel->salaireHistoriques()->create([
                'ancien_salaire' => null,
                'nouveau_salaire' => $personnel->salaire_base,
                'user_id' => auth()->id(),
            ]);
        });

        return redirect()->route('personnel.index')->with('status', 'Employé enregistré avec succès.');
    }

    public function show(Personnel $personnel): View
    {
        $personnel->load(['salaireHistoriques.user', 'bulletins', 'user', 'chauffeur']);

        return view('personnel.show', ['personnel' => $personnel]);
    }

    public function update(UpdatePersonnelRequest $request, Personnel $personnel): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($personnel, $data) {
            $ancien = (float) $personnel->salaire_base;

            $personnel->update($data);

            if ((float) $personnel->salaire_base !== $ancien) {
                $personnel->salaireHistoriques()->create([
                    'ancien_salaire' => $ancien,
                    'nouveau_salaire' => $personnel->salaire_base,
                    'user_id' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('personnel.index')->with('status', 'Employé mis à jour avec succès.');
    }

    public function destroy(Personnel $personnel): RedirectResponse
    {
        $personnel->delete();

        return back()->with('status', 'Employé supprimé avec succès.');
    }
}
