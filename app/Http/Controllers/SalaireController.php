<?php

namespace App\Http\Controllers;

use App\Models\Salaire;
use App\Models\Employe;
use App\Models\Presence;
use Illuminate\Http\Request;

class SalaireController extends Controller
{
    // Liste des salaires
    public function index()
    {
        $salaires = Salaire::with('employe')->get();
        return response()->json($salaires);
    }

    // Enregistrer un salaire
   // Enregistrer un salaire
public function store(Request $request)
{
    $validated = $request->validate([
        'mois_salaire' => 'required|string',
        'annee_salaire' => 'required|numeric',
        'salaire_base' => 'required|numeric',
        'primes_salaire' => 'nullable|numeric',
        'retenues_salaire' => 'nullable|numeric',
        'employe_id' => 'required|exists:employes,id_employe',
        'calcul_auto_retenues' => 'nullable|boolean',
    ]);

    // ⭐ VÉRIFIER SI UN SALAIRE EXISTE DÉJÀ POUR CE MOIS/ANNÉE/EMPLOYÉ
    $salaireExistant = Salaire::where('employe_id', $validated['employe_id'])
        ->where('mois_salaire', $validated['mois_salaire'])
        ->where('annee_salaire', $validated['annee_salaire'])
        ->first();

    if ($salaireExistant) {
        return response()->json([
            'message' => 'Un salaire existe déjà pour cet employé pour le mois de ' . $validated['mois_salaire'] . ' ' . $validated['annee_salaire']
        ], 422); // 422 = Unprocessable Entity
    }

    // Initialiser les valeurs par défaut
    $primes = $validated['primes_salaire'] ?? 0;
    $retenues = $validated['retenues_salaire'] ?? 0;

    // Si calcul automatique des retenues activé
    if ($request->input('calcul_auto_retenues', false)) {
        $retenues = $this->calculerRetenues(
            $validated['employe_id'],
            $validated['mois_salaire'],
            $validated['annee_salaire'],
            $validated['salaire_base']
        );
    }

    // Calcul du salaire net
    $salaire_net = $validated['salaire_base'] + $primes - $retenues;

    $salaire = Salaire::create([
        'mois_salaire' => $validated['mois_salaire'],
        'annee_salaire' => $validated['annee_salaire'],
        'salaire_base' => $validated['salaire_base'],
        'primes_salaire' => $primes,
        'retenues_salaire' => $retenues,
        'salaire_net' => $salaire_net,
        'employe_id' => $validated['employe_id'],
    ]);

    return response()->json([
        'message' => 'Salaire ajouté avec succès',
        'data' => $salaire
    ], 201);
}

    // Méthode pour calculer les retenues automatiquement
    private function calculerRetenues($employe_id, $mois, $annee, $salaire_base)
    {
        // Convertir le mois en nombre (Janvier = 1, Février = 2, etc.)
        $moisNum = $this->convertirMoisEnNumero($mois);
        
        if (!$moisNum) {
            return 0; // Si mois invalide, pas de retenues
        }

        // Récupérer toutes les présences du mois
        $presences = Presence::where('employe_id', $employe_id)
            ->whereYear('date_presence', $annee)
            ->whereMonth('date_presence', $moisNum)
            ->get();

        $nbAbsences = 0;
        $nbRetards = 0;

        foreach ($presences as $presence) {
            if ($presence->statut_presence === 'Absent') {
                $nbAbsences++;
            } elseif ($presence->statut_presence === 'En retard') {
                $nbRetards++;
            }
        }

        // Calcul des retenues
        // Vous pouvez ajuster ces valeurs selon vos règles
        $tauxJournalier = $salaire_base / 22; // 22 jours ouvrables par mois
        $retenueParAbsence = $tauxJournalier; // 1 jour de salaire par absence
        $retenueParRetard = $tauxJournalier * 0.25; // 25% d'un jour par retard

        $totalRetenues = ($nbAbsences * $retenueParAbsence) + ($nbRetards * $retenueParRetard);

        return round($totalRetenues, 2);
    }

    // Méthode pour obtenir les détails des retenues avant création
    public function calculerRetenuesPreview(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:employes,id_employe',
            'mois_salaire' => 'required|string',
            'annee_salaire' => 'required|numeric',
            'salaire_base' => 'required|numeric',
        ]);

        $moisNum = $this->convertirMoisEnNumero($request->mois_salaire);
        
        if (!$moisNum) {
            return response()->json(['error' => 'Mois invalide'], 400);
        }

        $presences = Presence::where('employe_id', $request->employe_id)
            ->whereYear('date_presence', $request->annee_salaire)
            ->whereMonth('date_presence', $moisNum)
            ->get();

        $nbAbsences = $presences->where('statut_presence', 'Absent')->count();
        $nbRetards = $presences->where('statut_presence', 'En retard')->count();

        $tauxJournalier = $request->salaire_base / 22;
        $retenueParAbsence = $tauxJournalier;
        $retenueParRetard = $tauxJournalier * 0.25;

        $totalRetenues = ($nbAbsences * $retenueParAbsence) + ($nbRetards * $retenueParRetard);

        return response()->json([
            'nb_absences' => $nbAbsences,
            'nb_retards' => $nbRetards,
            'retenue_par_absence' => round($retenueParAbsence, 2),
            'retenue_par_retard' => round($retenueParRetard, 2),
            'total_retenues' => round($totalRetenues, 2),
            'details_presences' => $presences
        ]);
    }

    // Convertir le nom du mois en numéro
    private function convertirMoisEnNumero($mois)
    {
        $moisMapping = [
            'janvier' => 1, 'février' => 2, 'mars' => 3, 'avril' => 4,
            'mai' => 5, 'juin' => 6, 'juillet' => 7, 'août' => 8,
            'septembre' => 9, 'octobre' => 10, 'novembre' => 11, 'décembre' => 12,
        ];

        return $moisMapping[strtolower($mois)] ?? null;
    }

    // Afficher un salaire
    public function show($id)
    {
        $salaire = Salaire::with('employe')->findOrFail($id);
        return response()->json($salaire);
    }

    // Mettre à jour un salaire
    public function update(Request $request, $id)
    {
        $salaire = Salaire::findOrFail($id);
        
        $validated = $request->validate([
            'mois_salaire' => 'sometimes|string',
            'annee_salaire' => 'sometimes|numeric',
            'salaire_base' => 'sometimes|numeric',
            'primes_salaire' => 'nullable|numeric',
            'retenues_salaire' => 'nullable|numeric',
            'employe_id' => 'sometimes|exists:employes,id_employe',
            'calcul_auto_retenues' => 'nullable|boolean',
        ]);

        // Si recalcul automatique demandé
        if ($request->input('calcul_auto_retenues', false)) {
            $validated['retenues_salaire'] = $this->calculerRetenues(
                $validated['employe_id'] ?? $salaire->employe_id,
                $validated['mois_salaire'] ?? $salaire->mois_salaire,
                $validated['annee_salaire'] ?? $salaire->annee_salaire,
                $validated['salaire_base'] ?? $salaire->salaire_base
            );
        }

        // Recalculer le salaire net si nécessaire
        if (isset($validated['salaire_base']) || isset($validated['primes_salaire']) || isset($validated['retenues_salaire'])) {
            $base = $validated['salaire_base'] ?? $salaire->salaire_base;
            $primes = $validated['primes_salaire'] ?? $salaire->primes_salaire;
            $retenues = $validated['retenues_salaire'] ?? $salaire->retenues_salaire;
            $validated['salaire_net'] = $base + $primes - $retenues;
        }

        $salaire->update($validated);

        return response()->json([
            'message' => 'Salaire mis à jour avec succès',
            'data' => $salaire
        ]);
    }

    // Supprimer un salaire
    public function destroy($id)
    {
        $salaire = Salaire::findOrFail($id);
        $salaire->delete();
        return response()->json(['message' => 'Salaire supprimé avec succès']);
    }
}