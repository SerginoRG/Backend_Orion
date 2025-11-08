<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    /**
     * Enregistrer une demande d'absence.
     */
    public function store(Request $request)
    {
        // ✅ Validation des champs
        $validated = $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif_absence' => 'required|string|max:255',
            'employe_id' => 'required|exists:employes,id_employe',
            'justificatif' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'message' => 'nullable|string|max:1000', //  ajouté
        ]);

        // ✅ Gestion du fichier justificatif
        $justificatifPath = null;
        if ($request->hasFile('justificatif')) {
            $file = $request->file('justificatif');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $justificatifPath = $file->storeAs('justificatifs', $fileName, 'public');
        }

        // ✅ Enregistrement dans la base
        $absence = Absence::create([
            'date_debut' => $validated['date_debut'],
            'date_fin' => $validated['date_fin'],
            'motif_absence' => $validated['motif_absence'],
            'employe_id' => $validated['employe_id'],
            'justificatif' => $justificatifPath,
            'statut_absence' => 'En attente',
            'message' => $validated['message'] ?? null, //  ajouté
        ]);

        return response()->json([
            'message' => 'Demande d’absence envoyée avec succès',
            'absence' => $absence,
        ], 201);
    }

    /**
     * Lister les absences d’un employé.
     */
    public function showByEmploye($id)
    {
        $absences = Absence::where('employe_id', $id)->get();
        return response()->json($absences);
    }

        public function destroy($id)
        {
            // Cherche l'absence par la clé primaire id_absence
            $absence = Absence::where('id_absence', $id)->first();

            if (!$absence) {
                return response()->json(['message' => 'Demande non trouvée'], 404);
            }

            // Supprime le justificatif si existant
            if ($absence->justificatif) {
                \Storage::disk('public')->delete($absence->justificatif);
            }

            $absence->delete();

            return response()->json(['message' => 'Demande supprimée avec succès']);
        }


}
