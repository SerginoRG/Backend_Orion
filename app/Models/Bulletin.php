<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    use HasFactory;

    protected $table = 'bulletins';
    protected $primaryKey = 'id_bulletin';
    public $timestamps = false;

    protected $fillable = [
        'reference_bulletin',
        'date_generation',
        'fichier_pdf',
        'salaire_id',
    ];

    // Relation avec Salaire
    public function salaire()
    {
        return $this->belongsTo(Salaire::class, 'salaire_id', 'id_salaire');
    }
}
