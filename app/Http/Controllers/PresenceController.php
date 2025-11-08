<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Employe;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresenceController extends Controller
{
    // Enregistrement de l'arrivée
    public function arrivee(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:employes,id_employe',
        ], [
            'employe_id.required' => 'L\'identifiant employé est requis.',
            'employe_id.exists' => 'Cet employé n\'existe pas dans la base de données.'
        ]);

        $now = Carbon::now();
        $date = $now->toDateString();
        $heure = $now->format('H:i');

        // Déterminer la période
        $periode = $now->hour < 12 ? 'matin' : 'apresmidi';

        // Déterminer le statut
        if ($periode === 'matin') {
            if ($heure >= '07:00' && $heure <= '08:15') {
                $statut = 'Présent';
            } elseif ($heure >= '08:16' && $heure <= '10:00') {
                $statut = 'En retard';
            } else {
                $statut = 'Absent';
            }
        } else {
            if ($heure >= '14:00' && $heure <= '15:15') {
                $statut = 'Présent';
            } elseif ($heure >= '15:16' && $heure <= '17:00') {
                $statut = 'En retard';
            } else {
                $statut = 'Absent';
            }
        }

        // Vérifie si une présence existe déjà pour aujourd'hui et cette période
        $presence = Presence::where('employe_id', $request->employe_id)
            ->whereDate('date_presence', $date)
            ->where('periode', $periode)
            ->whereNull('heure_depart')
            ->first();

        if ($presence) {
            return response()->json([
                'message' => 'Vous avez déjà enregistré votre arrivée pour cette période. Veuillez enregistrer votre départ.',
                'has_arrival' => true
            ], 409);
        }

        // Création de la présence
        $presence = Presence::create([
            'date_presence' => $date,
            'heure_arrivee' => $heure,
            'statut_presence' => $statut,
            'employe_id' => $request->employe_id,
            'periode' => $periode
        ]);

        return response()->json([
            'message' => 'Arrivée enregistrée avec succès.',
            'data' => $presence,
            'has_arrival' => true
        ], 201);
    }

    // Enregistrement du départ
    public function depart(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:employes,id_employe',
        ], [
            'employe_id.required' => 'L\'identifiant employé est requis.',
            'employe_id.exists' => 'Cet employé n\'existe pas dans la base de données.'
        ]);

        $now = Carbon::now();
        $date = $now->toDateString();
        $heure = $now->format('H:i');
        $periode = $now->hour < 12 ? 'matin' : 'apresmidi';

        // Récupère la présence du jour pour cette période sans départ
        $presence = Presence::where('employe_id', $request->employe_id)
            ->whereDate('date_presence', $date)
            ->where('periode', $periode)
            ->whereNull('heure_depart')
            ->latest('id_presence')
            ->first();

        if (!$presence) {
            return response()->json([
                'message' => 'Aucune arrivée trouvée pour cette période.',
                'has_arrival' => false
            ], 404);
        }

        $presence->update(['heure_depart' => $heure]);

        return response()->json([
            'message' => 'Départ enregistré avec succès.',
            'data' => $presence,
            'has_arrival' => false
        ], 200);
    }

    // Vérifier l'état de la présence actuelle
    public function checkStatus($employe_id)
    {
        $date = Carbon::now()->toDateString();
        $periode = Carbon::now()->hour < 12 ? 'matin' : 'apresmidi';

        $presence = Presence::where('employe_id', $employe_id)
            ->whereDate('date_presence', $date)
            ->where('periode', $periode)
            ->whereNull('heure_depart')
            ->latest('id_presence')
            ->first();

        return response()->json([
            'has_arrival' => $presence !== null,
            'presence' => $presence
        ]);
    }

    // Historique des présences de l'employé connecté
    public function historique($employe_id)
    {
        $presences = Presence::where('employe_id', $employe_id)
            ->orderBy('date_presence', 'desc')
            ->get();

        return response()->json($presences);
    }

    // Afficher toutes les présences (pour l'admin)
    public function index()
    {
        $presences = Presence::with('employe')
            ->orderBy('date_presence', 'desc')
            ->get();

        return response()->json($presences);
    }

    // Marquer les absents pour une période donnée
  public function marquerAbsents($periode)
        {
            $date = now()->toDateString();
            $now = now();

            // Définir les plages horaires
            $matinStart = Carbon::createFromTime(8, 0);
            $matinEnd   = Carbon::createFromTime(12, 0);

            $apremStart = Carbon::createFromTime(14, 0);
            $apremEnd   = Carbon::createFromTime(18, 0);

            // Vérifier période valide
            if (!in_array($periode, ['matin', 'apresmidi'])) {
                return response()->json(['error' => 'Période invalide'], 400);
            }

            $employes = Employe::all();
            $count = 0;

            foreach ($employes as $emp) {

                if ($periode === "matin") {

                    // A-t-il pointé entre 8h00 et 12h00 ?
                    $presenceExists = Presence::where('employe_id', $emp->id_employe)
                        ->whereDate('date_presence', $date)
                        ->where('periode', 'matin')
                        ->whereBetween('heure_arrivee', [$matinStart, $matinEnd])
                        ->exists();

                    if (!$presenceExists) {
                        Presence::firstOrCreate([
                            'date_presence'   => $date,
                            'employe_id'      => $emp->id_employe,
                            'periode'         => 'matin',
                        ], [
                            'statut_presence' => 'Absent',
                            'heure_arrivee'   => null,
                            'heure_depart'    => null,
                        ]);
                        $count++;
                    }
                }

                if ($periode === "apresmidi") {

                    // A-t-il pointé entre 14h00 et 18h00 ?
                    $presenceExists = Presence::where('employe_id', $emp->id_employe)
                        ->whereDate('date_presence', $date)
                        ->where('periode', 'apresmidi')
                        ->whereBetween('heure_arrivee', [$apremStart, $apremEnd])
                        ->exists();

                    if (!$presenceExists) {
                        Presence::firstOrCreate([
                            'date_presence'   => $date,
                            'employe_id'      => $emp->id_employe,
                            'periode'         => 'apresmidi',
                        ], [
                            'statut_presence' => 'Absent',
                            'heure_arrivee'   => null,
                            'heure_depart'    => null,
                        ]);
                        $count++;
                    }
                }
            }

            return response()->json([
                'message' => "Absents enregistrés.",
                'periode' => $periode,
                'nombre_absents' => $count,
                'date' => $date
            ], 200);
        }

}
