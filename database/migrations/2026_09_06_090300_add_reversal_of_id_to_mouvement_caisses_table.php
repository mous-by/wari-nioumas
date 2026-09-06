<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permet à un mouvement de caisse d'être la contre-passation (l'inverse)
 * d'un autre : utilisé pour annuler un mouvement automatique déjà comptée
 * (ex. une aide sociale payée puis annulée) sans jamais supprimer ni
 * modifier le mouvement d'origine.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvement_caisses', function (Blueprint $table) {
            $table->foreignId('reversal_of_id')->nullable()->constrained('mouvement_caisses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mouvement_caisses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reversal_of_id');
        });
    }
};
