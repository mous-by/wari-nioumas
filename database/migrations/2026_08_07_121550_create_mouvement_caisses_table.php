<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvement_caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caisse_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['entree', 'sortie']);
            $table->string('libelle');
            $table->decimal('montant', 12, 2);
            $table->date('date_mouvement');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['caisse_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvement_caisses');
    }
};
