<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chauffeur_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date_incident');
            $table->enum('type', ['panne', 'contravention', 'vol', 'agression', 'retard', 'autre'])->default('autre');
            $table->enum('gravite', ['leger', 'moyen', 'grave'])->default('leger');
            $table->text('description');
            $table->decimal('cout', 12, 2)->default(0);
            $table->text('decision')->nullable();
            $table->enum('statut', ['ouvert', 'resolu'])->default('ouvert');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['vehicule_id', 'date_incident']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
