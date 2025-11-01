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
        $date = Carbon::now()->toDateString();

        $plages = [
            'matin' => ['start' => '08:00', 'end' => '12:00'],
            'apresmidi' => ['start' => '15:00', 'end' => '18:00']
        ];

        if (!isset($plages[$periode])) {
            return response()->json(['error' => 'Période invalide.'], 400);
        }

        $start = $plages[$periode]['start'];
        $end = $plages[$periode]['end'];

        $presentIds = Presence::whereDate('date_presence', $date)
            ->where('periode', $periode)
            ->pluck('employe_id');

        $absents = Employe::whereNotIn('id_employe', $presentIds)->get();

        foreach ($absents as $employe) {
            $already = Presence::where('employe_id', $employe->id_employe)
                ->whereDate('date_presence', $date)
                ->where('statut_presence', 'Absent')
                ->where('periode', $periode)
                ->whereNull('heure_arrivee')
                ->whereBetween('created_at', [
                    Carbon::now()->startOfDay(),
                    Carbon::now()->endOfDay()
                ])
                ->exists();

            if (!$already) {
                Presence::create([
                    'date_presence' => $date,
                    'heure_arrivee' => null,
                    'heure_depart' => null,
                    'statut_presence' => 'Absent',
                    'employe_id' => $employe->id_employe,
                    'periode' => $periode
                ]);
            }
        }

        return response()->json([
            'message' => "Absents marqués pour la période : {$periode}",
            'periode' => $periode,
            'absents' => $absents
        ]);
    }
}
