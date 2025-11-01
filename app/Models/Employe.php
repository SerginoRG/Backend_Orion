<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;

    protected $table = 'employes';
    protected $primaryKey = 'id_employe';
    public $timestamps = false;

    protected $fillable = [
        'matricule_employe',
        'nom_employe',
        'prenom_employe',
        'date_naissance_employe',
        'cin_employe',
        'adresse_employe',
        'email_employe',
        'telephone_employe',
        'date_embauche_employe',
        'poste_employe',
        'salaire_base_employe',
        'photo_profil_employe',
        'service_id',
    ];

    // Relations
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id_service');
    }

    public function utilisateur()
    {
        return $this->hasOne(Utilisateur::class, 'employe_id', 'id_employe');
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'employe_id', 'id_employe');
    }

    public function presences()
    {
        return $this->hasMany(Presence::class, 'employe_id', 'id_employe');
    }

    public function absences()
    {
        return $this->hasMany(Absence::class, 'employe_id', 'id_employe');
    }
}
