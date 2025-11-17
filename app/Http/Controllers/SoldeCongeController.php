<?php

namespace App\Http\Controllers;

use App\Models\SoldeConge;
use Illuminate\Http\Request;

class SoldeCongeController extends Controller
{

     /**
     * Récupère tous les soldes de congé (pour DataTable).
     */
    public function index()
    {
        $soldes = SoldeConge::with('employe')->get(); // on récupère aussi les infos employé
        return response()->json($soldes);
    }

    /**
     * Récupère le solde de congé de l'année en cours pour un employé.
     */
    public function getSoldeByEmploye($id_employe)
    {
        $annee = date('Y');

        $solde = SoldeConge::where('employe_id', $id_employe)
            ->where('annee', $annee)
            ->first();

        if (!$solde) {
            return response()->json(['message' => 'Aucun solde trouvé pour cette année'], 404);
        }

        return response()->json($solde);
    }

    /**
     * Crée le solde d’un employé (appeler lors de la création employé).
     */
   public function store(Request $request)
    {
        $request->validate([
            'id_employe' => 'required|exists:employes,id_employe'
        ]);

        $annee = $request->annee ?? date('Y');

        // Évite les doublons
        if (SoldeConge::where('employe_id', $request->id_employe)->where('annee', $annee)->exists()) {
            return response()->json(['message' => 'Solde déjà créé pour cette année'], 409);
        }

        $solde = SoldeConge::create([
            'annee' => $annee,
            'jours_acquis' => 30,
            'jours_consommes' => 0,
            'jours_restants' => 30,
            'employe_id' => $request->id_employe,
        ]);

        return response()->json(['message' => 'Solde créé avec succès', 'solde' => $solde], 201);
    }

    /**
     * Supprime un solde de congé par son ID.
     */
    public function destroy($id)
    {
        $solde = SoldeConge::find($id);

        if (!$solde) {
            return response()->json(['message' => 'Solde introuvable'], 404);
        }

        $solde->delete();

        return response()->json(['message' => 'Solde supprimé avec succès']);
    }

}
