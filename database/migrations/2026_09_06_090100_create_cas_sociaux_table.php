<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cas_sociaux', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('personnel_id')->constrained()->restrictOnDelete();
            $table->foreignId('type_cas_social_id')->constrained('type_cas_sociaux')->restrictOnDelete();

            $table->date('date_cas');
            $table->date('date_demande');
            $table->text('motif');
            $table->text('observations')->nullable();

            $table->decimal('montant_demande', 12, 2);
            $table->decimal('montant_accorde', 12, 2)->nullable(); // fixé à l'approbation
            $table->enum('mode_paiement', ['especes', 'virement', 'mobile_money', 'cheque', 'autre'])->nullable();

            $table->enum('statut', ['brouillon', 'en_attente', 'approuve', 'paye', 'rejete', 'annule'])->default('brouillon');
            $table->text('motif_rejet')->nullable();
            $table->text('motif_annulation')->nullable();

            // Traçabilité : qui a fait quoi, quand (jamais de suppression de ce dossier).
            $table->foreignId('demandeur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('valide_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('date_validation')->nullable();
            $table->foreignId('paye_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('date_paiement')->nullable();
            $table->foreignId('annule_par_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('date_annulation')->nullable();

            $table->timestamps();

            $table->index('statut');
            $table->index('date_cas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cas_sociaux');
    }
};
