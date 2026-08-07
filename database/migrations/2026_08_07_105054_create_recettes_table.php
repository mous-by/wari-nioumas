<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recettes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chauffeur_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_recette');
            $table->decimal('montant_attendu', 12, 2)->default(0);
            $table->decimal('montant_verse', 12, 2)->default(0);
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['chauffeur_id', 'date_recette']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recettes');
    }
};
