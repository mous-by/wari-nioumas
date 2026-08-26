<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attestations_ventes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->enum('type_bien', ['vehicule', 'piece', 'equipement', 'autre']);

            // Champs véhicule (utilisés seulement si type_bien = vehicule)
            $table->string('marque')->nullable();
            $table->string('modele')->nullable();
            $table->string('immatriculation')->nullable();
            $table->string('numero_chassis')->nullable();
            $table->unsignedSmallInteger('annee')->nullable();
            $table->string('couleur')->nullable();
            $table->text('autres_caracteristiques')->nullable();

            // Champs pièce / équipement / autre
            $table->string('designation')->nullable();
            $table->string('reference')->nullable();
            $table->unsignedInteger('quantite')->nullable();
            $table->string('etat')->nullable();
            $table->text('description')->nullable();

            // Vendeur (Wari Niouma — société fixe, seul le représentant varie)
            $table->string('vendeur_representant');

            // Acheteur
            $table->string('acheteur_nom');
            $table->text('acheteur_adresse')->nullable();

            // Montants & paiement
            $table->decimal('montant_total', 14, 2);
            $table->decimal('montant_paye', 14, 2)->default(0);
            $table->enum('mode_paiement', ['especes', 'cheque', 'virement', 'autre'])->nullable();

            $table->date('date_vente');
            $table->string('lieu_vente');
            $table->text('observations')->nullable();

            $table->enum('statut', ['brouillon', 'validee'])->default('brouillon');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attestations_ventes');
    }
};
