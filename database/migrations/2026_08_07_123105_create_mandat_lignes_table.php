<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mandat_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mandat_paiement_id')->constrained('mandats_paiement')->cascadeOnDelete();
            $table->foreignId('personnel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bulletin_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('montant', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mandat_lignes');
    }
};
