<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Refonte du module Recettes en modèle « compte à rebours ».
 *
 * L'ancienne table `recettes` (une ligne par jour avec montant attendu/versé)
 * est remplacée par `versements` : on n'enregistre plus que les PAIEMENTS du
 * chauffeur. Le « montant dû » est désormais calculé automatiquement
 * (montant journalier de l'affectation × jours écoulés − jours d'absence
 * acceptée). Voir App\Models\Chauffeur::montantDu().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('recettes');

        Schema::create('versements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chauffeur_id')->constrained()->cascadeOnDelete();
            $table->date('date_versement');
            $table->decimal('montant', 12, 2);
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['chauffeur_id', 'date_versement']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versements');

        Schema::create('recettes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chauffeur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_recette');
            $table->decimal('montant_attendu', 12, 2)->default(0);
            $table->decimal('montant_verse', 12, 2)->default(0);
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['chauffeur_id', 'date_recette']);
        });
    }
};
