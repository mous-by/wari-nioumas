<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lien OPTIONNEL vers un véhicule déjà enregistré (module Véhicules), pour
 * pré-remplir ses infos à la création et marquer son état "vendu" à la
 * validation de l'attestation. Reste nullable : l'attestation fonctionne
 * sans, pour un bien jamais enregistré dans l'app (véhicule ou non).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations_ventes', function (Blueprint $table) {
            $table->foreignId('vehicule_id')->nullable()->after('type_bien')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attestations_ventes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicule_id');
        });
    }
};
