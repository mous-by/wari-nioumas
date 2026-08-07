<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnels', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('telephone')->nullable();
            $table->string('poste');
            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->string('banque')->nullable();
            $table->string('numero_compte')->nullable();
            $table->date('date_embauche')->nullable();
            $table->enum('statut', ['actif', 'inactif'])->default('actif');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chauffeur_id')->nullable()->constrained()->nullOnDelete();
            $table->text('observations')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnels');
    }
};
