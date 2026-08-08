<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            // Périodicité du montant : journalier (camions, comportement existant)
            // ou forfait mensuel / trimestriel / semestriel (camionettes).
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
