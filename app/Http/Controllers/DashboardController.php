<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Service;
use App\Models\Presence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        // Nombre total d'employés
        $totalEmployes = Employe::count();

        // Nombre total de services
        $totalServices = Service::count();

        // Statistiques des absences et retards du jour
        $today = Carbon::now()->toDateString();
        
        $absences = Presence::whereDate('date_presence', $today)
            ->where('statut_presence', 'Absent')
            ->count();
        
        $retards = Presence::whereDate('date_presence', $today)
            ->where('statut_presence', 'En retard')
            ->count();

        // Calcul du pourcentage d'absences/retards
        $totalPresencesToday = Presence::whereDate('date_presence', $today)->count();
        $tauxAbsenceRetard = $totalPresencesToday > 0 
            ? round((($absences + $retards) / $totalPresencesToday) * 100, 1) 
            : 0;

        return response()->json([
            'total_employes' => $totalEmployes,
            'total_services' => $totalServices,
            'absences' => $absences,
            'retards' => $retards,
            'taux_absence_retard' => $tauxAbsenceRetard,
        ], 200);
    }

    // Statistiques des présences sur les 7 derniers jours
    public function getPresenceStats()
    {
        $startDate = Carbon::now()->subDays(6)->toDateString();
        $endDate = Carbon::now()->toDateString();

        $stats = Presence::selectRaw('
    date_presence,
    SUM(CASE WHEN statut_presence = \'Présent\' THEN 1 ELSE 0 END) as presents,
    SUM(CASE WHEN statut_presence = \'En retard\' THEN 1 ELSE 0 END) as retards,
    SUM(CASE WHEN statut_presence = \'Absent\' THEN 1 ELSE 0 END) as absents
')

            ->whereBetween('date_presence', [$startDate, $endDate])
            ->groupBy('date_presence')
            ->orderBy('date_presence', 'asc')
            ->get();

        // Formater les données pour le graphique
        $formattedStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $stat = $stats->firstWhere('date_presence', $date);
            
            $formattedStats[] = [
                'date' => Carbon::parse($date)->format('d/m'),
                'Présents' => $stat ? (int)$stat->presents : 0,
                'Retards' => $stat ? (int)$stat->retards : 0,
                'Absents' => $stat ? (int)$stat->absents : 0,
            ];
        }

        return response()->json($formattedStats, 200);
    }

    // Masse salariale par service
    public function getMasseSalarialeParService()
    {
        $stats = Service::leftJoin('employes', 'services.id_service', '=', 'employes.service_id')
            ->selectRaw('
                services.nom_service,
                COALESCE(SUM(employes.salaire_base_employe), 0) as masse_salariale,
                COUNT(employes.id_employe) as nombre_employes
            ')
            ->groupBy('services.id_service', 'services.nom_service')
            ->orderBy('masse_salariale', 'desc')
            ->get();

        $formattedStats = $stats->map(function ($stat) {
            return [
                'service' => $stat->nom_service,
                'masse_salariale' => (float)$stat->masse_salariale,
                'nombre_employes' => (int)$stat->nombre_employes,
            ];
        });

        return response()->json($formattedStats, 200);
    }
}