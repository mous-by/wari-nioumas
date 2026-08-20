<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Retire ce qui avait été préparé pour un modèle "reliquat de voyage" basé
 * sur les versements — remplacé par la table `voyages` (chaque voyage
 * accumule son montant au total du chauffeur, indépendamment des
 * versements/Recettes).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('versements', 'affectation_id')) {
            Schema::table('versements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('affectation_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('versements', function (Blueprint $table) {
            $table->foreignId('affectation_id')->nullable()->after('chauffeur_id')
                ->constrained()->nullOnDelete();
        });
    }
};
