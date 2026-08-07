<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permet de lier un mouvement de caisse à sa source automatique
 * (un Versement = entrée, une Dépense = sortie), pour l'alimentation
 * automatique de la caisse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvement_caisses', function (Blueprint $table) {
            $table->nullableMorphs('source');
        });
    }

    public function down(): void
    {
        Schema::table('mouvement_caisses', function (Blueprint $table) {
            $table->dropMorphs('source');
        });
    }
};
