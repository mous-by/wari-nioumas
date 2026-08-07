<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulletins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('periode_mois');
            $table->unsignedSmallInteger('periode_annee');
            $table->decimal('salaire_base', 12, 2)->default(0);
            $table->decimal('primes', 12, 2)->default(0);
            $table->decimal('retenues', 12, 2)->default(0);
            $table->decimal('net_a_payer', 12, 2)->default(0);
            $table->text('observations')->nullable();
            $table->enum('statut', ['brouillon', 'valide', 'paye'])->default('brouillon');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['personnel_id', 'periode_mois', 'periode_annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletins');
    }
};
