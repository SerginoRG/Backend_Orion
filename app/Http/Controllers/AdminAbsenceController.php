<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Http\Request;
use App\Models\Notification;

class AdminAbsenceController extends Controller
{
    /**
     * Retourne toutes les absences avec les informations de l'employé.
     */
    public function index()
    {
        try {
            // Récupère toutes les absences avec la relation employé
            $absences = Absence::with('employe')->orderBy('id_absence', 'desc')->get();

            return response()->json($absences, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des absences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function updateStatut(Request $request, $id)
{
    $request->validate([
        'statut_absence' => 'required|in:En attente,Validée,Refusée',
    ]);

    $absence = Absence::with('employe')->findOrFail($id);
    $absence->statut_absence = $request->statut_absence;
    $absence->save();

    // ✅ Création de la notification
    Notification::create([
        'employe_id' => $absence->employe_id,
        'titre' => "Mise à jour de votre demande d'absence",
        'message' => "Votre demande d'absence du " . $absence->date_debut . " au " . $absence->date_fin . " a été " . strtolower($absence->statut_absence) . ".",
    ]);

    return response()->json(['message' => 'Statut mis à jour avec succès']);
}

}