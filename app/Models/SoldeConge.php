<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldeConge extends Model
{
    use HasFactory;

     protected $table = 'solde_conge';
    protected $primaryKey = 'id_solde';
    public $timestamps = false;

   protected $fillable = [
        'annee',
        'jours_acquis',
        'jours_consommes',
        'jours_restants',
        'employe_id',
    ];
    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }

}
