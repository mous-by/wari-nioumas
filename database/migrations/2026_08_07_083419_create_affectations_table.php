<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('chauffeur_id')->constrained()->cascadeOnDelete();
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->string('motif_fin')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
