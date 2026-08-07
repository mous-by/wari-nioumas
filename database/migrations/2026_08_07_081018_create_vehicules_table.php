<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            $table->string('immatriculation')->unique();
            $table->string('marque');
            $table->string('modele');
            $table->string('type');
            $table->unsignedSmallInteger('annee')->nullable();
            $table->enum('etat', ['actif', 'non_actif', 'vendu', 'garage'])->default('actif');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
