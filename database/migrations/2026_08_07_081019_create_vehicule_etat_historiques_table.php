<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicule_etat_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->constrained()->cascadeOnDelete();
            $table->string('ancien_etat')->nullable();
            $table->string('nouveau_etat');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicule_etat_historiques');
    }
};
