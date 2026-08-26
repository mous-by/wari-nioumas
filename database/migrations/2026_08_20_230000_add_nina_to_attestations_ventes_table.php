<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attestations_ventes', function (Blueprint $table) {
            $table->string('vendeur_nina')->nullable()->after('vendeur_representant');
            $table->string('acheteur_nina')->nullable()->after('acheteur_nom');
        });
    }

    public function down(): void
    {
        Schema::table('attestations_ventes', function (Blueprint $table) {
            $table->dropColumn(['vendeur_nina', 'acheteur_nina']);
        });
    }
};
