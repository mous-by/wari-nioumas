<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->decimal('montant_journalier', 12, 2)->default(0)->after('chauffeur_id');
        });
    }

    public function down(): void
    {
        Schema::table('affectations', function (Blueprint $table) {
            $table->dropColumn('montant_journalier');
        });
    }
};
