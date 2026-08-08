<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE affectations MODIFY periodicite ENUM('journalier', 'hebdomadaire', 'mensuel', 'trimestriel', 'semestriel') NOT NULL DEFAULT 'journalier'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE affectations MODIFY periodicite ENUM('journalier', 'mensuel', 'trimestriel', 'semestriel') NOT NULL DEFAULT 'journalier'");
    }
};
