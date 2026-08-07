<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignatureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SignatureController extends Controller
{
    public function edit(): View
    {
        return view('configuration.signature', ['user' => auth()->user()]);
    }

    public function update(SignatureRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Signature : soit dessinée (data URL base64), soit fichier importé.
        if ($request->filled('signature_data') && str_starts_with($request->input('signature_data'), 'data:image')) {
            $this->remplacer($user, 'signature', $this->depuisDataUrl($request->input('signature_data'), 'signatures'));
        } elseif ($request->hasFile('signature_file')) {
            $this->remplacer($user, 'signature', $request->file('signature_file')->store('signatures', 'public'));
        }

        // Cachet : fichier importé.
        if ($request->hasFile('cachet_file')) {
            $this->remplacer($user, 'cachet', $request->file('cachet_file')->store('cachets', 'public'));
        }

        return back()->with('status', 'Signature et cachet mis à jour avec succès.');
    }

    private function remplacer($user, string $champ, string $nouveauChemin): void
    {
        if ($user->{$champ}) {
            Storage::disk('public')->delete($user->{$champ});
        }

        $user->update([$champ => $nouveauChemin]);
    }

    /**
     * Décode une image dessinée (data URL) et l'enregistre sur le disque public.
     */
    private function depuisDataUrl(string $dataUrl, string $dossier): string
    {
        [$meta, $contenu] = explode(',', $dataUrl, 2);
        $binaire = base64_decode($contenu);

        $chemin = $dossier.'/'.uniqid('sig_', true).'.png';
        Storage::disk('public')->put($chemin, $binaire);

        return $chemin;
    }
}
