<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Absence;
use App\Models\Accident;
use App\Models\Affectation;
use App\Models\Bulletin;
use App\Models\Caisse;
use App\Models\Depense;
use App\Models\Incident;
use App\Models\MandatPaiement;
use App\Models\MouvementCaisse;
use App\Models\User;
use App\Models\Validation;
use App\Models\Versement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $query = User::with('roles')->latest();

        if (! auth()->user()->hasRole('superadmin')) {
            $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'superadmin'));
        }

        return view('users.index', [
            'users' => $query->get(),
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $roleName = $request->validated('role');

        $user = User::create($request->safe()->except('role'));
        $user->assignRole($roleName);

        // Les rôles ne portent aucune permission : on attribue directement à
        // l'utilisateur le jeu de permissions par défaut de son rôle. Il pourra
        // ensuite être ajusté finement via « Assigner permissions ».
        $user->syncPermissions(config("role_permissions.{$roleName}", []));

        return redirect()
            ->route('user-permissions.index', ['user' => $user->id])
            ->with('status', 'Utilisateur créé. Voici ses permissions par défaut, ajustez-les si besoin.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->guardAgainstSuperadmin($user);

        $data = $request->safe()->except(['role', 'password']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $user->update($data);

        if (! $user->hasRole('superadmin')) {
            $user->syncRoles($request->validated('role'));
        }

        return redirect()->route('users.index')->with('status', 'Utilisateur mis à jour avec succès.');
    }

    public function toggleActif(User $user): RedirectResponse
    {
        $this->guardAgainstSuperadmin($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['actif' => ! $user->actif]);

        return back()->with('status', $user->actif ? 'Compte activé.' : 'Compte désactivé.');
    }

    /**
     * Supprime un utilisateur ET, en cascade, tout ce qu'il a enregistré
     * (versements, dépenses, accidents, absences, caisse, bulletins, mandats…).
     * Réservé par défaut au superadmin (qui contourne tout via Gate::before) ;
     * la permission « utilisateurs.supprimer » peut être donnée à un DG au besoin.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->hasRole('superadmin')) {
            return back()->with('error', 'Un compte superadmin ne peut pas être supprimé.');
        }

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        DB::transaction(function () use ($user) {
            $id = $user->id;

            // Tout ce que l'utilisateur a « fait » part avec lui (cascade applicative).
            Versement::where('user_id', $id)->delete();
            Depense::where('user_id', $id)->delete();
            Accident::where('user_id', $id)->delete();
            Incident::where('user_id', $id)->delete();
            Absence::where('user_id', $id)->delete();
            Affectation::where('user_id', $id)->delete();
            MouvementCaisse::where('user_id', $id)->delete();
            Caisse::where('user_id', $id)->delete();           // cascade DB -> ses mouvements
            Bulletin::where('user_id', $id)->delete();
            MandatPaiement::where('user_id', $id)->delete();   // cascade DB -> ses lignes
            Validation::where('demandeur_id', $id)->delete();

            // Les autres références (historiques, signataire, valideur, lien
            // Personnel↔compte) sont détachées automatiquement (nullOnDelete).
            $user->delete();
        });

        return redirect()->route('users.index')->with('status', 'Utilisateur et toutes ses données ont été supprimés.');
    }

    private function assignableRoles()
    {
        return Role::where('name', '!=', 'superadmin')->pluck('name');
    }

    private function guardAgainstSuperadmin(User $user): void
    {
        if ($user->hasRole('superadmin') && ! auth()->user()->hasRole('superadmin')) {
            throw new AuthorizationException('Ce compte ne peut pas être modifié.');
        }
    }
}
