<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnel_salaire_historiques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personnel_id')->constrained()->cascadeOnDelete();
            $table->decimal('ancien_salaire', 12, 2)->nullable();
            $table->decimal('nouveau_salaire', 12, 2);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnel_salaire_historiques');
    }
};
