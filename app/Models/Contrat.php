<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;

    protected $table = 'contrats';
    protected $primaryKey = 'id_contrat';
    public $timestamps = false;

    protected $fillable = [
        'type_contrat',
        'date_debut_contrat',
        'date_fin_contrat',
        'statut_contrat',
        'employe_id',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }
}
