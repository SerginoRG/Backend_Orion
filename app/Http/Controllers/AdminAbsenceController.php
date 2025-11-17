<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Notification;
use App\Models\SoldeConge;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminAbsenceController extends Controller
{
    public function index()
    {
        try {
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
                ['jours_acquis' => 30, 'jours_consommes' => 0, 'jours_restants' => 30]
            );

            // 🔹 Calcul du nombre total de jours demandés
            $jours = Carbon::parse($absence->date_debut)->diffInDays(Carbon::parse($absence->date_fin)) + 1;

            /*
            |--------------------------------------------------------------------------
            | 🔒 Vérification selon le type de congé
            |--------------------------------------------------------------------------
            */

            // 🧭 Cas 1 : Congé maternité
            if ($absence->motif_absence === 'Congé maternité') {
                $maxMaternite = 98; // 14 semaines

                if ($jours > $maxMaternite) {
                    return response()->json([
                        'message' => 'Le congé maternité ne peut pas dépasser 14 semaines (98 jours).'
                    ], 400);
                }
                // ➕ Pas de déduction sur le solde de congé (CNaPS prend en charge)
            }

            // 🧭 Cas 2 : Congé paternité
            elseif ($absence->motif_absence === 'Congé paternité') {
                $maxPaternite = 10; // 10 jours

                if ($jours > $maxPaternite) {
                    return response()->json([
                        'message' => 'Le congé paternité ne peut pas dépasser 10 jours.'
                    ], 400);
                }
                // ➕ Pas de déduction sur le solde de congé
            }

            // 🧭 Cas 3 : Congé pour décès
            elseif ($absence->motif_absence === 'Congé pour décès') {
                $maxDeces = 3; // 3 jours

                if ($jours > $maxDeces) {
                    return response()->json([
                        'message' => 'Le congé pour décès ne peut pas dépasser 3 jours.'
                    ], 400);
                }
                // ➕ Pas de déduction sur le solde de congé
            }

            // 🧭 Cas 4 : Permission (max 3 jours)
            elseif ($absence->motif_absence === 'Permission') {

                $joursPermissionMax = 3;

                if ($jours <= $joursPermissionMax) {
                    // Pas de déduction
                } else {
                    $joursEnTrop = $jours - $joursPermissionMax;

                    if ($solde->jours_restants < $joursEnTrop) {
                        return response()->json([
                            'message' => 'Solde congé insuffisant pour compléter la permission dépassée.'
                        ], 400);
                    }

                    $solde->jours_consommes += $joursEnTrop;
                    $solde->jours_restants = $solde->jours_acquis - $solde->jours_consommes;
                }
            }

            // 🧭 Cas 5 : Congé normal
            elseif ($absence->motif_absence === 'Congé') {

                // Vérifie le solde de congé
                if ($solde->jours_restants < $jours) {
                    return response()->json(['message' => 'Solde congé insuffisant'], 400);
                }

                $solde->jours_consommes += $jours;
                $solde->jours_restants = $solde->jours_acquis - $solde->jours_consommes;
            }

            // 🧭 Cas 6 : Maladie → pas de déduction
            elseif ($absence->motif_absence === 'Maladie') {
                // Rien à faire
            }

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

    public function generatePDF($id)
    {
        $absence = Absence::with('employe')->findOrFail($id);
        $jours = Carbon::parse($absence->date_debut)->diffInDays(Carbon::parse($absence->date_fin)) + 1;

        $pdf = Pdf::loadView('pdf.absence', [
            'absence' => $absence,
            'jours'   => $jours
        ]);

        return $pdf->download('Attestation_Absence_' . $absence->employe->nom_employe . '.pdf');
    }
}
