<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chauffeurs', function (Blueprint $table) {
            // NINA devient facultatif. L'index unique existant est conservé
            // (MySQL autorise plusieurs valeurs NULL dans un index unique).
            $table->string('nina')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('chauffeurs', function (Blueprint $table) {
            $table->string('nina')->nullable(false)->change();
        });
    }
};
