<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            // Polymorphique : un document appartient à un véhicule (carte grise,
            // assurance, vignette...) ou à un chauffeur (permis de conduite).
            $table->morphs('documentable');
            $table->string('type_document');
            // Libellé libre uniquement quand type_document = "autre".
            $table->string('libelle_autre')->nullable();
            $table->string('numero')->nullable();
            $table->date('date_etablissement')->nullable();
            $table->date('date_expiration')->nullable();
            $table->string('fichier')->nullable();
            $table->string('nom_original')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('date_expiration');
        });

        // Reprise des permis déjà enregistrés sur les chauffeurs existants :
        // sans cela, un chauffeur créé avant cette fonctionnalité n'apparaîtrait
        // jamais dans la surveillance des échéances tant que son permis n'est
        // pas modifié une première fois.
        $chauffeurs = DB::table('chauffeurs')
            ->whereNull('deleted_at')
            ->select('id', 'permis_numero', 'permis_date_validite')
            ->get();

        $maintenant = now();

        foreach ($chauffeurs as $chauffeur) {
            DB::table('documents')->insert([
                'documentable_type' => 'App\\Models\\Chauffeur',
                'documentable_id' => $chauffeur->id,
                'type_document' => 'permis_conduite',
                'numero' => $chauffeur->permis_numero,
                'date_expiration' => $chauffeur->permis_date_validite,
                'created_at' => $maintenant,
                'updated_at' => $maintenant,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
