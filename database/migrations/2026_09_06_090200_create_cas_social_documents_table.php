<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cas_social_documents', function (Blueprint $table) {
            $table->id();
            // ->constrained() sans argument devinerait la table "cas_socials"
            // (pluriel anglais naïf) : la vraie table s'appelle "cas_sociaux".
            $table->foreignId('cas_social_id')->constrained('cas_sociaux')->cascadeOnDelete();
            $table->string('type_document');
            $table->string('chemin');
            $table->string('nom_original');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('cas_social_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cas_social_documents');
    }
};
