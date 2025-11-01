<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;

    protected $table = 'presences';
    protected $primaryKey = 'id_presence';
    public $timestamps = false;

    protected $fillable = [
        'date_presence',
        'heure_arrivee',
        'heure_depart',
        'statut_presence',
        'employe_id',
         'periode', // <- ajouter
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }
}
