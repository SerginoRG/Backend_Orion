<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Notification;
use App\Models\SoldeConge;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminAbsenceController extends Controller
{
    /**
     * Retourne toutes les absences avec les informations de l'employé.
     */
    public function index()
    {
        try {
            // Récupère toutes les absences avec la relation employé
            $absences = Absence::with('employe')
                               ->orderBy('id_absence', 'desc')
                               ->get();

            return response()->json($absences, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des absences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour le statut d'une absence et ajuste automatiquement le solde de congé si validé.
     */
    /**
     * Met à jour le statut d'une absence.
     */
  public function updateStatut(Request $request, $id)
    {
        $request->validate([
            'statut_absence' => 'required|in:En attente,Validée,Refusée',
        ]);

        $absence = Absence::with('employe')->findOrFail($id);
        $ancienStatut = $absence->statut_absence;
        $absence->statut_absence = $request->statut_absence;

        if ($request->statut_absence === 'Validée' && $ancienStatut !== 'Validée') {

            $annee = Carbon::parse($absence->date_debut)->year;

            $solde = SoldeConge::firstOrCreate(
                ['employe_id' => $absence->employe_id, 'annee' => $annee],
                ['jours_acquis' => 30, 'jours_consommes' => 0, 'jours_restants' => 30,
                'jours_permission_max' => 3, 'jours_permission_utilises' => 0, 'jours_permission_restants' => 3]
            );

            $jours = Carbon::parse($absence->date_debut)->diffInDays(Carbon::parse($absence->date_fin)) + 1;

            // ✅ Cas Permission avec dépassement automatique
            if ($absence->motif_absence === 'Permission') {

                // Si les jours demandés sont <= au reste → tout passe en Permission
                if ($solde->jours_permission_restants >= $jours) {
                    $solde->jours_permission_utilises += $jours;
                    $solde->jours_permission_restants = $solde->jours_permission_max - $solde->jours_permission_utilises;
                } 
                else {
                    // Jours restant pour permission
                    $permissionPossible = $solde->jours_permission_restants;
                    $joursEnTrop = $jours - $permissionPossible;

                    // On consomme tout le quota permission
                    $solde->jours_permission_utilises = $solde->jours_permission_max;
                    $solde->jours_permission_restants = 0;

                    // Vérification du solde de congés pour les jours en surplus
                    if ($solde->jours_restants >= $joursEnTrop) {
                        // On déduit les jours restants des congés
                        $solde->jours_consommes += $joursEnTrop;
                        $solde->jours_restants = $solde->jours_acquis - $solde->jours_consommes;
                    } 
                    else {
                        // Impossible → Absence non justifiée
                        // On raccourcit l'absence validée à ce qui est autorisé
                        $joursNonJustifies = $joursEnTrop - $solde->jours_restants;

                        // Consomme ce qu’il reste en congés
                        if ($solde->jours_restants > 0) {
                            $solde->jours_consommes += $solde->jours_restants;
                            $solde->jours_restants = 0;
                        }

                        // On convertit l’absence en Absence non justifiée
                        $absence->motif_absence = "Absence non justifiée";
                    }
                }
            }

            // ✅ Cas Congé normal (inchangé)
            elseif ($absence->motif_absence === 'Congé') {
                if ($solde->jours_restants < $jours) {
                    return response()->json(['message' => 'Solde congé insuffisant'], 400);
                }
                $solde->jours_consommes += $jours;
                $solde->jours_restants = $solde->jours_acquis - $solde->jours_consommes;
            }

            // ✅ Cas Maladie → Aucun décompte
            // (Ne rien faire)

            $solde->save();
        }

        $absence->save();

        Notification::create([
            'employe_id' => $absence->employe_id,
            'titre' => "Mise à jour de votre demande d'absence",
            'message' => "Votre demande d'absence du {$absence->date_debut} au {$absence->date_fin} a été " . strtolower($absence->statut_absence) . ".",
        ]);

        return response()->json(['message' => 'Statut mis à jour avec succès']);
    }

}
