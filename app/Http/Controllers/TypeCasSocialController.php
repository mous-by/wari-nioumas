<?php

namespace App\Http\Controllers;

use App\Models\TypeCasSocial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypeCasSocialController extends Controller
{
    public function index(): View
    {
        return view('cas_sociaux.types.index', [
            'types' => TypeCasSocial::orderBy('libelle')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', 'unique:type_cas_sociaux,libelle'],
            'description' => ['nullable', 'string'],
        ]);

        TypeCasSocial::create($validated);

        return back()->with('status', 'Type de cas social ajouté avec succès.');
    }

    public function update(Request $request, TypeCasSocial $type): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255', 'unique:type_cas_sociaux,libelle,'.$type->id],
            'description' => ['nullable', 'string'],
        ]);

        $type->update($validated);

        return back()->with('status', 'Type de cas social mis à jour avec succès.');
    }

    public function toggle(TypeCasSocial $type): RedirectResponse
    {
        $type->update(['actif' => ! $type->actif]);

        return back()->with('status', $type->actif ? 'Type activé.' : 'Type désactivé.');
    }
}
