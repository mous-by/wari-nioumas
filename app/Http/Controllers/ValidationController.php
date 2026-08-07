<?php

namespace App\Http\Controllers;

use App\Models\Validation;
use App\Support\Approbation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ValidationController extends Controller
{
    public function index(): View
    {
        return view('validations.index', [
            'enAttente' => Validation::enAttente()->with('demandeur')->latest()->get(),
            'traitees' => Validation::where('statut', '!=', 'en_attente')->with(['demandeur', 'valideur'])->latest('decidee_at')->limit(30)->get(),
        ]);
    }

    public function approuver(Validation $validation): RedirectResponse
    {
        if ($validation->statut !== 'en_attente') {
            return back()->withErrors(['validation' => 'Cette demande a déjà été traitée.']);
        }

        DB::transaction(function () use ($validation) {
            $validation->update([
                'statut' => 'approuvee',
                'valideur_id' => auth()->id(),
                'decidee_at' => now(),
            ]);

            Approbation::executer($validation);
        });

        return back()->with('status', 'Demande approuvée et exécutée.');
    }

    public function refuser(Request $request, Validation $validation): RedirectResponse
    {
        if ($validation->statut !== 'en_attente') {
            return back()->withErrors(['validation' => 'Cette demande a déjà été traitée.']);
        }

        $validation->update([
            'statut' => 'refusee',
            'valideur_id' => auth()->id(),
            'motif' => $request->input('motif'),
            'decidee_at' => now(),
        ]);

        return back()->with('status', 'Demande refusée.');
    }
}
