<?php

namespace App\Http\Controllers;

use App\Models\Salaire;
use App\Models\Employe;
use App\Models\Presence;
use App\Models\Absence;
use App\Models\Notification;
use Illuminate\Http\Request;

class SalaireController extends Controller
{
    // Liste des motifs d'absence qui ne génèrent PAS de retenue
    private $motifsAutorises = [
        'Congé',
        'Maladie',
        'Permission',
        'Congé maternité',
        'Congé paternité',
        'Congé pour décès'
    ];

    // Liste des salaires
    public function index()
    {
        $salaires = Salaire::with('employe')->get();
        return response()->json($salaires);
    }

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
            'cnaps' => 'nullable|numeric',
            'medical' => 'nullable|numeric',
            'irsa' => 'nullable|numeric',
            'salaire_net' => 'nullable|numeric',
        ]);

        // Vérifier si un salaire existe déjà
        $salaireExistant = Salaire::where('employe_id', $validated['employe_id'])
            ->where('mois_salaire', $validated['mois_salaire'])
            ->where('annee_salaire', $validated['annee_salaire'])
            ->first();

        if ($salaireExistant) {
            return response()->json([
                'message' => 'Un salaire existe déjà pour cet employé pour le mois de ' . $validated['mois_salaire'] . ' ' . $validated['annee_salaire']
            ], 422);
        }

        // Initialiser les valeurs
        $primes = $validated['primes_salaire'] ?? 0;
        $retenues = $validated['retenues_salaire'] ?? 0;

        //  NE recalculer que si calcul_auto_retenues ET que retenues non fourni
        if ($request->input('calcul_auto_retenues', false) && $retenues == 0) {
            $retenues = $this->calculerRetenues(
                $validated['employe_id'],
                $validated['mois_salaire'],
                $validated['annee_salaire'],
                $validated['salaire_base']
            );
        }

        // Calcul du salaire brut (base + primes)
        $salaireBrut = $validated['salaire_base'] + $primes;

        // Utiliser les valeurs manuelles si fournies, sinon calculer automatiquement
        $cnaps   = $validated['cnaps'] ?? 0;
        $medical = $validated['medical'] ?? 0;
        $irsa    = $validated['irsa'] ?? 0;

        $salaire_net = $validated['salaire_net'] ?? ($salaireBrut - $cnaps - $medical - $irsa - $retenues);

        $salaire = Salaire::create([
            'mois_salaire' => $validated['mois_salaire'],
            'annee_salaire' => $validated['annee_salaire'],
            'salaire_base' => $validated['salaire_base'],
            'primes_salaire' => $primes,
            'retenues_salaire' => $retenues,
            'cnaps' => $cnaps,
            'medical' => $medical,
            'irsa' => $irsa,
            'salaire_net' => $salaire_net,
            'employe_id' => $validated['employe_id'],
        ]);

        // Notification
        $employe = Employe::find($validated['employe_id']);
        Notification::create([
            'employe_id' => $validated['employe_id'],
            'titre' => "Salaire du mois disponible",
            'message' => "Bonjour " . $employe->nom_employe . ", votre salaire du mois de " . $validated['mois_salaire'] . " est maintenant prêt.",
        ]);

        return response()->json([
            'message' => 'Salaire ajouté avec succès',
            'data' => $salaire
        ], 201);
    }

    // Calculer les retenues (absences/retards basé sur les présences)
    private function calculerRetenues($employe_id, $mois, $annee, $salaire_base)
    {
        $moisNum = $this->convertirMoisEnNumero($mois);
        if (!$moisNum) return 0;

        // Récupérer les présences du mois
        $presences = Presence::where('employe_id', $employe_id)
            ->whereYear('date_presence', $annee)
            ->whereMonth('date_presence', $moisNum)
            ->get();

        // Récupérer les absences autorisées du mois
        $absencesAutorisees = Absence::where('employe_id', $employe_id)
            ->where('statut_absence', 'Validée')
            ->whereIn('motif_absence', $this->motifsAutorises)
            ->where(function($query) use ($annee, $moisNum) {
                $query->whereYear('date_debut', $annee)
                      ->whereMonth('date_debut', $moisNum);
            })
            ->orWhere(function($query) use ($annee, $moisNum) {
                $query->whereYear('date_fin', $annee)
                      ->whereMonth('date_fin', $moisNum);
            })
            ->get();

        // Créer une liste des dates avec absences justifiées
        $datesJustifiees = [];
        foreach ($absencesAutorisees as $absence) {
            $dateDebut = new \DateTime($absence->date_debut);
            $dateFin = new \DateTime($absence->date_fin);
            
            while ($dateDebut <= $dateFin) {
                $datesJustifiees[] = $dateDebut->format('Y-m-d');
                $dateDebut->modify('+1 day');
            }
        }

        $heuresAbsence = 0;
        $heuresRetard = 0;

        foreach ($presences as $p) {
            $datePresence = date('Y-m-d', strtotime($p->date_presence));
            
            // Vérifier si cette absence est justifiée
            $estJustifiee = in_array($datePresence, $datesJustifiees);

            // Chaque période (matin ou après-midi) = 4 heures
            if ($p->statut_presence === 'Absent' && !$estJustifiee) {
                $heuresAbsence += 4; // 4h par période absente NON justifiée
            }

            if ($p->statut_presence === 'En retard') {
                // Pénalité fixe de 1h par retard
                $heuresRetard += 1;
            }
        }

        // Calcul du taux horaire basé sur 22 jours * 8 heures
        $tauxHoraire = $salaire_base / (22 * 8);

        // Retenue finale
        $totalRetenues = ($heuresAbsence + $heuresRetard) * $tauxHoraire;

        return round($totalRetenues, 2);
    }

    // Preview des retenues avec détails
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

        // Récupérer les présences
        $presences = Presence::where('employe_id', $request->employe_id)
            ->whereYear('date_presence', $request->annee_salaire)
            ->whereMonth('date_presence', $moisNum)
            ->get();

        // Récupérer les absences autorisées
        $absencesAutorisees = Absence::where('employe_id', $request->employe_id)
            ->where('statut_absence', 'Validée')
            ->whereIn('motif_absence', $this->motifsAutorises)
            ->where(function($query) use ($request, $moisNum) {
                $query->whereYear('date_debut', $request->annee_salaire)
                      ->whereMonth('date_debut', $moisNum);
            })
            ->orWhere(function($query) use ($request, $moisNum) {
                $query->whereYear('date_fin', $request->annee_salaire)
                      ->whereMonth('date_fin', $moisNum);
            })
            ->get();

        // Créer une liste des dates avec absences justifiées
        $datesJustifiees = [];
        foreach ($absencesAutorisees as $absence) {
            $dateDebut = new \DateTime($absence->date_debut);
            $dateFin = new \DateTime($absence->date_fin);
            
            while ($dateDebut <= $dateFin) {
                $datesJustifiees[] = $dateDebut->format('Y-m-d');
                $dateDebut->modify('+1 day');
            }
        }

        // Comptage avec distinction justifié/non justifié
        $nbAbsencesMatin = 0;
        $nbAbsencesApresMidi = 0;
        $nbAbsencesMatinJustifiees = 0;
        $nbAbsencesApresMidiJustifiees = 0;
        $nbRetards = 0;

        foreach ($presences as $p) {
            $datePresence = date('Y-m-d', strtotime($p->date_presence));
            $estJustifiee = in_array($datePresence, $datesJustifiees);

            if ($p->statut_presence === 'Absent') {
                if ($p->periode === 'matin') {
                    $nbAbsencesMatin++;
                    if ($estJustifiee) $nbAbsencesMatinJustifiees++;
                } elseif ($p->periode === 'apresmidi') {
                    $nbAbsencesApresMidi++;
                    if ($estJustifiee) $nbAbsencesApresMidiJustifiees++;
                }
            }

            if ($p->statut_presence === 'En retard') {
                $nbRetards++;
            }
        }

        // Calcul des absences NON justifiées seulement
        $nbAbsencesMatinNonJustifiees = $nbAbsencesMatin - $nbAbsencesMatinJustifiees;
        $nbAbsencesApresMidiNonJustifiees = $nbAbsencesApresMidi - $nbAbsencesApresMidiJustifiees;

        // Calcul des heures (seulement les non justifiées)
        $heuresAbsence = ($nbAbsencesMatinNonJustifiees + $nbAbsencesApresMidiNonJustifiees) * 4;
        $heuresRetard = $nbRetards * 1;

        // Calcul du taux horaire
        $tauxHoraire = $request->salaire_base / (22 * 8);

        // Calcul des retenues
        $retenueAbsences = $heuresAbsence * $tauxHoraire;
        $retenueRetards = $heuresRetard * $tauxHoraire;
        $totalRetenues = $retenueAbsences + $retenueRetards;

        return response()->json([
            'nb_absences_matin' => $nbAbsencesMatin,
            'nb_absences_apresmidi' => $nbAbsencesApresMidi,
            'nb_absences_matin_justifiees' => $nbAbsencesMatinJustifiees,
            'nb_absences_apresmidi_justifiees' => $nbAbsencesApresMidiJustifiees,
            'nb_absences_matin_non_justifiees' => $nbAbsencesMatinNonJustifiees,
            'nb_absences_apresmidi_non_justifiees' => $nbAbsencesApresMidiNonJustifiees,
            'nb_absences_total' => $nbAbsencesMatin + $nbAbsencesApresMidi,
            'nb_absences_justifiees_total' => $nbAbsencesMatinJustifiees + $nbAbsencesApresMidiJustifiees,
            'heures_absence' => $heuresAbsence,
            'nb_retards' => $nbRetards,
            'heures_retard' => $heuresRetard,
            'taux_horaire' => round($tauxHoraire, 2),
            'retenue_absences' => round($retenueAbsences, 2),
            'retenue_retards' => round($retenueRetards, 2),
            'total_retenues' => round($totalRetenues, 2),
            'absences_justifiees' => $absencesAutorisees->map(function($a) {
                return [
                    'date_debut' => $a->date_debut,
                    'date_fin' => $a->date_fin,
                    'motif' => $a->motif_absence,
                    'message' => $a->message
                ];
            }),
            'details_presences' => $presences->map(function($p) use ($datesJustifiees) {
                $datePresence = date('Y-m-d', strtotime($p->date_presence));
                return [
                    'date' => $p->date_presence,
                    'periode' => $p->periode,
                    'statut' => $p->statut_presence,
                    'heure_arrivee' => $p->heure_arrivee,
                    'heure_depart' => $p->heure_depart,
                    'justifiee' => in_array($datePresence, $datesJustifiees)
                ];
            })
        ]);
    }

    // Optionnel : Fonction pour calculer les heures de retard exactes
    private function calculerHeuresRetard($heureArrivee, $heureTheorique)
    {
        try {
            $arrivee = new \DateTime($heureArrivee);
            $theorique = new \DateTime($heureTheorique);
            $diff = $arrivee->diff($theorique);
            
            // Convertir en heures décimales
            return $diff->h + ($diff->i / 60);
        } catch (\Exception $e) {
            return 1; // Valeur par défaut en cas d'erreur
        }
    }

    // Convertir mois en numéro
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

        if ($request->input('calcul_auto_retenues', false)) {
            $validated['retenues_salaire'] = $this->calculerRetenues(
                $validated['employe_id'] ?? $salaire->employe_id,
                $validated['mois_salaire'] ?? $salaire->mois_salaire,
                $validated['annee_salaire'] ?? $salaire->annee_salaire,
                $validated['salaire_base'] ?? $salaire->salaire_base
            );
        }

        // Recalculer les cotisations si nécessaire
        if (isset($validated['salaire_base']) || isset($validated['primes_salaire'])) {
            $base = $validated['salaire_base'] ?? $salaire->salaire_base;
            $primes = $validated['primes_salaire'] ?? $salaire->primes_salaire;
            $retenues = $validated['retenues_salaire'] ?? $salaire->retenues_salaire;
            
            $salaireBrut = $base + $primes;
            $validated['cnaps'] = $validated['cnaps'] ?? $salaire->cnaps;
            $validated['medical'] = $validated['medical'] ?? $salaire->medical;
            $validated['irsa'] = $validated['irsa'] ?? $salaire->irsa;

            // Calcul salaire net basé uniquement sur les valeurs fournies
            $base = $validated['salaire_base'] ?? $salaire->salaire_base;
            $primes = $validated['primes_salaire'] ?? $salaire->primes_salaire;
            $retenues = $validated['retenues_salaire'] ?? $salaire->retenues_salaire;

            $validated['salaire_net'] = $base + $primes - $validated['cnaps'] - $validated['medical'] - $validated['irsa'] - $retenues;
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