<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\UtilisateurController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\SalaireController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\AdminAbsenceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SoldeCongeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API pour Admin
Route::post('/login', [AdminController::class, 'login']);

// CRUD API pour Service
Route::get('/services', [ServiceController::class, 'index']);       // Lire
Route::post('/services', [ServiceController::class, 'store']);       // Créer
Route::get('/services/{id}', [ServiceController::class, 'show']);    // Lire 1 service
Route::put('/services/{id}', [ServiceController::class, 'update']);  // Modifier
Route::delete('/services/{id}', [ServiceController::class, 'destroy']); // Supprimer

// CRUD Employé
Route::get('/employes/{service_id}', [EmployeController::class, 'index']);   // Lister par service
Route::get('/employe/{id}', [EmployeController::class, 'show']);             // Afficher un employé
Route::post('/employes', [EmployeController::class, 'store']);               // Créer
Route::put('/employes/{id}', [EmployeController::class, 'update']);          // Modifier
Route::delete('/employes/{id}', [EmployeController::class, 'destroy']);      // Supprimer

// CRUD Contrat
Route::get('/contrats', [ContratController::class, 'index']);
Route::get('/contrats/{id}', [ContratController::class, 'show']);
Route::post('/contrats', [ContratController::class, 'store']);
Route::put('/contrats/{id}', [ContratController::class, 'update']);
Route::delete('/contrats/{id}', [ContratController::class, 'destroy']);

// Liste tous les employés (pour le select)
Route::get('/employes', [EmployeController::class, 'all']);
// Contrat par employé
Route::get('/contrats/employe/{employe_id}', [ContratController::class, 'getByEmploye']);


Route::get('/utilisateurs', [UtilisateurController::class, 'index']);
Route::post('/utilisateurs', [UtilisateurController::class, 'store']);
Route::get('/utilisateurs/{id}', [UtilisateurController::class, 'show']);
Route::put('/utilisateurs/{id}', [UtilisateurController::class, 'update']);
Route::delete('/utilisateurs/{id}', [UtilisateurController::class, 'destroy']);

// route spéciale pour changer le statut via le tableau
Route::put('/utilisateurs/{id}/statut', [UtilisateurController::class, 'updateStatut']);

Route::post('/utilisateurslogin', [UtilisateurController::class, 'login']);

Route::get('/utilisateurs/{id}/profil', [UtilisateurController::class, 'profil']);


Route::post('/presence/arrivee', [PresenceController::class, 'arrivee']);
Route::post('/presence/depart', [PresenceController::class, 'depart']);
Route::get('/presence/historique/{employe_id}', [PresenceController::class, 'historique']);


Route::post('/absences', [AbsenceController::class, 'store']); // Ajouter une demande
Route::get('/absences/employe/{id}', [AbsenceController::class, 'showByEmploye']); // Voir les absences par employé



Route::get('/admin/presences', [PresenceController::class, 'index']);



// AVANT les autres routes de salaires (important pour l'ordre)
Route::post('/salaires/calculer-retenues-preview', [SalaireController::class, 'calculerRetenuesPreview']);

// Puis les routes normales
Route::get('/salaires', [SalaireController::class, 'index']);
Route::post('/salaires', [SalaireController::class, 'store']);
Route::get('/salaires/{id}', [SalaireController::class, 'show']);
Route::put('/salaires/{id}', [SalaireController::class, 'update']);
Route::delete('/salaires/{id}', [SalaireController::class, 'destroy']);

// Ajouter cette route dans votre fichier api.php
Route::get('/presence/check-status/{employe_id}', [PresenceController::class, 'checkStatus']);

Route::prefix('bulletins')->group(function () {
    Route::get('/', [BulletinController::class, 'index']);
    Route::post('/', [BulletinController::class, 'store']);
   
   
    Route::delete('/{id}', [BulletinController::class, 'destroy']);
});
Route::post('/bulletins/{id}/upload', [BulletinController::class, 'update']);

Route::get('/bulletins/{id}/generate-pdf', [App\Http\Controllers\BulletinController::class, 'genererPDF']);




Route::post('/admin/marquer-absents/{periode}', [PresenceController::class, 'marquerAbsents']);



// Route pour afficher toutes les absences côté admin
Route::get('/admin/absences', [AdminAbsenceController::class, 'index']);

Route::put('/admin/absences/{id}/statut', [AdminAbsenceController::class, 'updateStatut']);

// routes/api.php
Route::get('/user/{id}/notifications', function($id) {
    return \App\Models\Notification::where('employe_id', $id)
        ->orderBy('created_at', 'desc')
        ->get();
});

Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy']);

// Nombre notifications non lues
Route::get('/user/{id}/notifications/unread-count', function ($id) {
    return \App\Models\Notification::where('employe_id', $id)
        ->where('is_read', 0)
        ->count();
});

Route::put('/user/{id}/notifications/mark-read', function($id) {
    \App\Models\Notification::where('employe_id', $id)
        ->where('is_read', 0)
        ->update(['is_read' => 1]);

    return response()->json(['message' => 'Notifications marquées comme lues']);
});


Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
Route::get('/dashboard/presence-stats', [DashboardController::class, 'getPresenceStats']);
Route::get('/dashboard/masse-salariale', [DashboardController::class, 'getMasseSalarialeParService']);




Route::middleware('auth:sanctum')->group(function () {
    // Ajouter une absence

    // Lister toutes les absences d'un employé
    Route::get('/user/{id}/absences', [AbsenceController::class, 'showByEmploye']);

  
});

Route::delete('/absences/{id}', [AbsenceController::class, 'destroy']);

// Solde-conge
Route::post('/solde-conge/create', [SoldeCongeController::class, 'store']);
Route::get('/solde-conge/{id_employe}', [SoldeCongeController::class, 'getSoldeByEmploye']);
// Supprimer un solde par son ID
Route::delete('/solde-conge/{id}', [SoldeCongeController::class, 'destroy']);

// Récupérer tous les soldes de congé (pour afficher dans le tableau)
Route::get('/solde-conge', [SoldeCongeController::class, 'index']);
