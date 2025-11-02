<?php
// app/Console/Commands/VerifierAbsenceAutomatique.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employe;
use App\Models\Presence;
use App\Models\Absence;
use Carbon\Carbon;

class VerifierAbsenceAutomatique extends Command
{
    protected $signature = 'absence:verifier';
    protected $description = 'Créer automatiquement les absences si un employé n\'a pas pointé';

    public function handle()
    {
        $now = Carbon::now();
        $date = $now->toDateString();

        $matinStart = Carbon::createFromTime(8, 0);
        $matinEnd = Carbon::createFromTime(12, 0);

        $apremStart = Carbon::createFromTime(15, 0);
        $apremEnd = Carbon::createFromTime(18, 0);

        $employes = Employe::all();

        foreach ($employes as $emp) {

            // Vérification matin (entre 12h00 et 12h30)
            if ($now->between($matinEnd, $matinEnd->copy()->addMinutes(30))) {

                $presence = Presence::where('employe_id', $emp->id_employe)
                    ->whereDate('date_presence', $date)
                    ->whereBetween('heure_arrivee', [$matinStart, $matinEnd])
                    ->exists();

                if (!$presence) {
                    Absence::firstOrCreate([
                        'employe_id' => $emp->id_employe,
                        'date_absence' => $date,
                        'periode' => 'matin',
                        'motif' => 'Absence automatique (non pointé matin)',
                    ]);
                }
            }

            // Vérification après-midi (entre 18h00 et 18h30)
            if ($now->between($apremEnd, $apremEnd->copy()->addMinutes(30))) {

                $presence = Presence::where('employe_id', $emp->id_employe)
                    ->whereDate('date_presence', $date)
                    ->whereBetween('heure_arrivee', [$apremStart, $apremEnd])
                    ->exists();

                if (!$presence) {
                    Absence::firstOrCreate([
                        'employe_id' => $emp->id_employe,
                        'date_absence' => $date,
                        'periode' => 'apresmidi',
                        'motif' => 'Absence automatique (non pointé après-midi)',
                    ]);
                }
            }
        }

        return Command::SUCCESS;
    }
}
