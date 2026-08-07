<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chauffeurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone')->unique();
            $table->string('adresse')->nullable();
            $table->string('nina')->unique();
            $table->string('permis_numero');
            $table->date('permis_date_validite');
            $table->date('date_embauche');
            $table->enum('statut', ['actif', 'inactif', 'suspendu'])->default('actif');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chauffeurs');
    }
};
