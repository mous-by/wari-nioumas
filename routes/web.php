<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AccidentController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\CaisseController;
use App\Http\Controllers\ChauffeurController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\MandatController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\ValidationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecetteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\VehiculeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('home');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    Route::get('/configuration/signature', [\App\Http\Controllers\SignatureController::class, 'edit'])->name('signature.edit');
    Route::put('/configuration/signature', [\App\Http\Controllers\SignatureController::class, 'update'])->name('signature.update');

    // Documentation / guide d'utilisation (accessible à tous les utilisateurs connectés)
    Route::view('/documentation', 'documentation')->name('documentation');

    Route::middleware('permission:utilisateurs.voir')->group(function () {
        Route::get('/utilisateurs', [UserController::class, 'index'])->name('users.index');
    });

    Route::middleware('permission:utilisateurs.creer')->group(function () {
        Route::post('/utilisateurs', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('permission:utilisateurs.modifier')->group(function () {
        Route::put('/utilisateurs/{user}', [UserController::class, 'update'])->name('users.update');
    });

    Route::middleware('permission:utilisateurs.desactiver')->group(function () {
        Route::patch('/utilisateurs/{user}/desactiver', [UserController::class, 'toggleActif'])->name('users.toggle-actif');
    });

    Route::middleware('permission:utilisateurs.supprimer')->group(function () {
        Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // Le référentiel des permissions (catalogue + création) est réservé au superadmin.
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    });

    Route::middleware('permission:roles.gerer')->group(function () {
        Route::get('/assigner-permissions', [UserPermissionController::class, 'index'])->name('user-permissions.index');
        Route::put('/assigner-permissions/{user}', [UserPermissionController::class, 'update'])->name('user-permissions.update');
    });

    Route::middleware('permission:chauffeurs.voir')->group(function () {
        Route::get('/chauffeurs', [ChauffeurController::class, 'index'])->name('chauffeurs.index');
        Route::get('/chauffeurs/{chauffeur}', [ChauffeurController::class, 'show'])->name('chauffeurs.show');
    });

    Route::middleware('permission:chauffeurs.creer')->group(function () {
        Route::post('/chauffeurs', [ChauffeurController::class, 'store'])->name('chauffeurs.store');
    });

    Route::middleware('permission:chauffeurs.modifier')->group(function () {
        Route::put('/chauffeurs/{chauffeur}', [ChauffeurController::class, 'update'])->name('chauffeurs.update');
    });

    Route::middleware('permission:chauffeurs.supprimer')->group(function () {
        Route::delete('/chauffeurs/{chauffeur}', [ChauffeurController::class, 'destroy'])->name('chauffeurs.destroy');
    });

    Route::middleware('permission:vehicules.voir')->group(function () {
        Route::get('/vehicules', [VehiculeController::class, 'index'])->name('vehicules.index');
        Route::get('/vehicules/{vehicule}', [VehiculeController::class, 'show'])->name('vehicules.show');
    });

    Route::middleware('permission:vehicules.creer')->group(function () {
        Route::post('/vehicules', [VehiculeController::class, 'store'])->name('vehicules.store');
    });

    Route::middleware('permission:vehicules.modifier')->group(function () {
        Route::put('/vehicules/{vehicule}', [VehiculeController::class, 'update'])->name('vehicules.update');
    });

    Route::middleware('permission:vehicules.supprimer')->group(function () {
        Route::delete('/vehicules/{vehicule}', [VehiculeController::class, 'destroy'])->name('vehicules.destroy');
    });

    Route::middleware('permission:affectations.voir')->group(function () {
        Route::get('/affectations', [AffectationController::class, 'index'])->name('affectations.index');
    });

    Route::middleware('permission:affectations.creer')->group(function () {
        Route::post('/affectations', [AffectationController::class, 'store'])->name('affectations.store');
    });

    Route::middleware('permission:affectations.modifier')->group(function () {
        Route::put('/affectations/{affectation}', [AffectationController::class, 'update'])->name('affectations.update');
    });

    Route::middleware('permission:affectations.terminer')->group(function () {
        Route::patch('/affectations/{affectation}/terminer', [AffectationController::class, 'terminer'])->name('affectations.terminer');
    });

    Route::middleware('permission:affectations.supprimer')->group(function () {
        Route::delete('/affectations/{affectation}', [AffectationController::class, 'destroy'])->name('affectations.destroy');
    });

    Route::middleware('permission:recettes.voir')->group(function () {
        Route::get('/recettes', [RecetteController::class, 'index'])->name('recettes.index');
    });

    Route::middleware('permission:recettes.creer')->group(function () {
        Route::post('/recettes', [RecetteController::class, 'store'])->name('recettes.store');
    });

    Route::middleware('permission:recettes.modifier')->group(function () {
        Route::put('/recettes/{versement}', [RecetteController::class, 'update'])->name('recettes.update');
    });

    Route::middleware('permission:recettes.supprimer')->group(function () {
        Route::delete('/recettes/{versement}', [RecetteController::class, 'destroy'])->name('recettes.destroy');
    });

    Route::middleware('permission:absences.voir')->group(function () {
        Route::get('/absences', [AbsenceController::class, 'index'])->name('absences.index');
    });

    Route::middleware('permission:absences.creer')->group(function () {
        Route::post('/absences', [AbsenceController::class, 'store'])->name('absences.store');
    });

    Route::middleware('permission:absences.modifier')->group(function () {
        Route::put('/absences/{absence}', [AbsenceController::class, 'update'])->name('absences.update');
    });

    Route::middleware('permission:absences.valider')->group(function () {
        Route::patch('/absences/{absence}/accepter', [AbsenceController::class, 'accepter'])->name('absences.accepter');
        Route::patch('/absences/{absence}/refuser', [AbsenceController::class, 'refuser'])->name('absences.refuser');
    });

    Route::middleware('permission:absences.supprimer')->group(function () {
        Route::delete('/absences/{absence}', [AbsenceController::class, 'destroy'])->name('absences.destroy');
    });

    Route::middleware('permission:depenses.voir')->group(function () {
        Route::get('/depenses', [DepenseController::class, 'index'])->name('depenses.index');
    });

    Route::middleware('permission:depenses.creer')->group(function () {
        Route::post('/depenses', [DepenseController::class, 'store'])->name('depenses.store');
    });

    Route::middleware('permission:depenses.modifier')->group(function () {
        Route::put('/depenses/{depense}', [DepenseController::class, 'update'])->name('depenses.update');
    });

    Route::middleware('permission:depenses.supprimer')->group(function () {
        Route::delete('/depenses/{depense}', [DepenseController::class, 'destroy'])->name('depenses.destroy');
    });

    Route::middleware('permission:accidents.voir')->group(function () {
        Route::get('/accidents', [AccidentController::class, 'index'])->name('accidents.index');
        Route::get('/accidents/{accident}', [AccidentController::class, 'show'])->name('accidents.show');
    });

    Route::middleware('permission:accidents.creer')->group(function () {
        Route::post('/accidents', [AccidentController::class, 'store'])->name('accidents.store');
    });

    Route::middleware('permission:accidents.modifier')->group(function () {
        Route::put('/accidents/{accident}', [AccidentController::class, 'update'])->name('accidents.update');
    });

    Route::middleware('permission:accidents.supprimer')->group(function () {
        Route::delete('/accidents/{accident}', [AccidentController::class, 'destroy'])->name('accidents.destroy');
    });

    Route::middleware('permission:incidents.voir')->group(function () {
        Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');
        Route::get('/incidents/{incident}', [IncidentController::class, 'show'])->name('incidents.show');
    });

    Route::middleware('permission:incidents.creer')->group(function () {
        Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
    });

    Route::middleware('permission:incidents.modifier')->group(function () {
        Route::put('/incidents/{incident}', [IncidentController::class, 'update'])->name('incidents.update');
    });

    Route::middleware('permission:incidents.supprimer')->group(function () {
        Route::delete('/incidents/{incident}', [IncidentController::class, 'destroy'])->name('incidents.destroy');
    });

    // Module 10 — Caisse
    Route::middleware('permission:caisse.voir')->group(function () {
        Route::get('/caisse', [CaisseController::class, 'index'])->name('caisse.index');
    });

    Route::middleware('permission:caisse.ouvrir')->group(function () {
        Route::post('/caisse/ouvrir', [CaisseController::class, 'ouvrir'])->name('caisse.ouvrir');
    });

    Route::middleware('permission:caisse.mouvementer')->group(function () {
        Route::post('/caisse/{caisse}/mouvements', [CaisseController::class, 'mouvement'])->name('caisse.mouvement');
        Route::delete('/caisse/mouvements/{mouvement}', [CaisseController::class, 'destroyMouvement'])->name('caisse.mouvement.destroy');
    });

    Route::middleware('permission:caisse.fermer')->group(function () {
        Route::patch('/caisse/{caisse}/fermer', [CaisseController::class, 'fermer'])->name('caisse.fermer');
    });

    // Module 11 — Finances
    Route::middleware('permission:finances.voir')->group(function () {
        Route::get('/finances', [FinanceController::class, 'index'])->name('finances.index');
        Route::get('/finances/export/pdf', [FinanceController::class, 'exportPdf'])->name('finances.export.pdf');
        Route::get('/finances/export/csv', [FinanceController::class, 'exportCsv'])->name('finances.export.csv');
    });

    // Module 12 — Tableaux de bord / Statistiques
    Route::middleware('permission:rapports.voir')->group(function () {
        Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');
    });

    // Validations (réservées au Directeur général)
    Route::middleware('permission:validations.voir')->group(function () {
        Route::get('/validations', [ValidationController::class, 'index'])->name('validations.index');
        Route::patch('/validations/{validation}/approuver', [ValidationController::class, 'approuver'])->name('validations.approuver');
        Route::patch('/validations/{validation}/refuser', [ValidationController::class, 'refuser'])->name('validations.refuser');
    });

    // Module Salaires — Personnel
    Route::middleware('permission:personnel.voir')->group(function () {
        Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel.index');
        Route::get('/personnel/{personnel}', [PersonnelController::class, 'show'])->name('personnel.show');
    });
    Route::middleware('permission:personnel.creer')->group(function () {
        Route::post('/personnel', [PersonnelController::class, 'store'])->name('personnel.store');
    });
    Route::middleware('permission:personnel.modifier')->group(function () {
        Route::put('/personnel/{personnel}', [PersonnelController::class, 'update'])->name('personnel.update');
    });
    Route::middleware('permission:personnel.supprimer')->group(function () {
        Route::delete('/personnel/{personnel}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');
    });

    // Module Salaires — Bulletins
    Route::middleware('permission:bulletins.voir')->group(function () {
        Route::get('/bulletins', [BulletinController::class, 'index'])->name('bulletins.index');
        Route::get('/bulletins/{bulletin}/pdf', [BulletinController::class, 'pdf'])->name('bulletins.pdf');
    });
    Route::middleware('permission:bulletins.gerer')->group(function () {
        Route::post('/bulletins', [BulletinController::class, 'store'])->name('bulletins.store');
        Route::post('/bulletins/generer-mois', [BulletinController::class, 'genererMois'])->name('bulletins.generer-mois');
        Route::put('/bulletins/{bulletin}', [BulletinController::class, 'update'])->name('bulletins.update');
        Route::delete('/bulletins/{bulletin}', [BulletinController::class, 'destroy'])->name('bulletins.destroy');
    });

    // Module Salaires — Mandats de paiement
    Route::middleware('permission:mandats.voir')->group(function () {
        Route::get('/mandats', [MandatController::class, 'index'])->name('mandats.index');
        Route::get('/mandats/{mandat}', [MandatController::class, 'show'])->name('mandats.show');
        Route::get('/mandats/{mandat}/pdf', [MandatController::class, 'pdf'])->name('mandats.pdf');
    });
    Route::middleware('permission:mandats.gerer')->group(function () {
        Route::post('/mandats', [MandatController::class, 'store'])->name('mandats.store');
        Route::patch('/mandats/{mandat}/statut', [MandatController::class, 'changerStatut'])->name('mandats.statut');
        Route::delete('/mandats/{mandat}', [MandatController::class, 'destroy'])->name('mandats.destroy');
    });
    Route::middleware('permission:mandats.signer')->group(function () {
        Route::patch('/mandats/{mandat}/signer', [MandatController::class, 'signer'])->name('mandats.signer');
    });
});
