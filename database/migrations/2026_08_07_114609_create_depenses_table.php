<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('categorie', [
                'carburant',
                'entretien',
                'reparation',
                'pneus',
                'assurance',
                'visite_technique',
                'autres',
            ]);
            $table->decimal('montant', 12, 2);
            $table->date('date_depense');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicule_id', 'date_depense']);
            $table->index('categorie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
