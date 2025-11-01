<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utilisateur extends Model
{
    use HasFactory;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'id_utilisateur';
    public $timestamps = false;

    protected $fillable = [
        'nom_utilisateur',
        'password_utilisateur',
        'statut_utilisateur',
        'employe_id',
    ];

    protected $hidden = [
        'password_utilisateur',
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class, 'employe_id', 'id_employe');
    }
}
