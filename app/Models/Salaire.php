<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salaire extends Model
{
    use HasFactory;
    
    protected $table = 'salaires';
    protected $primaryKey = 'id_salaire';
    public $timestamps = false; // Ajoutez cette ligne si pas de timestamps
    
    protected $fillable = [
        'mois_salaire',
        'annee_salaire',
        'salaire_base',
        'primes_salaire',
        'retenues_salaire',
        'salaire_net',
        'employe_id',
    ];


    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }
}


