<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();
            $table->decimal('solde_ouverture', 12, 2)->default(0);
            $table->dateTime('date_ouverture');
            $table->decimal('solde_fermeture', 12, 2)->nullable();
            $table->dateTime('date_fermeture')->nullable();
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte');
            $table->text('observations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
