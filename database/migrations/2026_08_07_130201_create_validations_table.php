<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes de validation soumises au Directeur général : certaines actions
 * sensibles (ex. sortie d'argent de la caisse) effectuées par un rôle autre
 * que le DG doivent d'abord être approuvées par lui.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validations', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('libelle');
            $table->foreignId('demandeur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->enum('statut', ['en_attente', 'approuvee', 'refusee'])->default('en_attente');
            $table->foreignId('valideur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motif')->nullable();
            $table->dateTime('decidee_at')->nullable();
            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validations');
    }
};
