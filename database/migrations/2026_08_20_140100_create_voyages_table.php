<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un voyage = un trajet ponctuel effectué sous une affectation de type
 * "voyage". Chaque voyage a sa propre date et son propre montant (saisi
 * manuellement) ; le total des voyages d'un chauffeur s'accumule
 * automatiquement (somme de tous ses voyages, toutes affectations
 * confondues) — voir Chauffeur::totalVoyages().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voyages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affectation_id')->constrained()->cascadeOnDelete();
            $table->date('date_voyage');
            $table->decimal('montant', 12, 2);
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voyages');
    }
};
