<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            // Périodicité du montant, choisie par affectation : journalier
            // (comportement existant) ou forfait mensuel / trimestriel /
            // semestriel (ex. camionettes, qui ne se paient pas au jour).
            $table->enum('periodicite', ['journalier', 'mensuel', 'trimestriel', 'semestriel'])
                ->default('journalier')
                ->after('montant_journalier');
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn('periodicite');
        });
    }
};
