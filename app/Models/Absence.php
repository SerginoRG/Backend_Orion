<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    use HasFactory;

    protected $table = 'absences';
    protected $primaryKey = 'id_absence';
    public $timestamps = false;

    protected $fillable = [
        'date_debut',
        'date_fin',
        'motif_absence',
        'statut_absence',
        'justificatif',
        'employe_id',
        'message', // ajouté
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }
}