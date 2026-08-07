<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chauffeur_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_accident');
            $table->string('lieu')->nullable();
            $table->enum('gravite', ['leger', 'moyen', 'grave'])->default('leger');
            $table->enum('responsabilite', ['chauffeur', 'tiers', 'partagee', 'indeterminee'])->default('indeterminee');
            $table->text('description');
            $table->decimal('cout_reparation', 12, 2)->default(0);
            $table->text('decision')->nullable();
            $table->enum('statut', ['en_cours', 'clos'])->default('en_cours');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicule_id', 'date_accident']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accidents');
    }
};
