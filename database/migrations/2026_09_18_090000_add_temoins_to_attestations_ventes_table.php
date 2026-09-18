<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations_ventes', function (Blueprint $table) {
            // Bloc "témoins" optionnel : désactivé par défaut, donc les
            // attestations existantes ne changent pas d'aspect.
            $table->boolean('avec_temoins')->default(false)->after('acheteur_adresse');
            $table->string('temoin_1_nom')->nullable()->after('avec_temoins');
            $table->string('temoin_2_nom')->nullable()->after('temoin_1_nom');
        });
    }

    public function down(): void
    {
        Schema::table('attestations_ventes', function (Blueprint $table) {
            $table->dropColumn(['avec_temoins', 'temoin_1_nom', 'temoin_2_nom']);
        });
    }
};
