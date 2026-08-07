<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandats_paiement', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('date_mandat');
            $table->string('banque')->nullable();
            $table->unsignedTinyInteger('periode_mois');
            $table->unsignedSmallInteger('periode_annee');
            $table->decimal('montant_total', 14, 2)->default(0);
            $table->enum('statut', ['brouillon', 'signe', 'depose', 'paye'])->default('brouillon');
            $table->foreignId('signataire_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('date_signature')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandats_paiement');
    }
};
