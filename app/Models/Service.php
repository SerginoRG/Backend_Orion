<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    protected $primaryKey = 'id_service';
    public $timestamps = false;
    

    protected $fillable = [
        'nom_service',
        'description_service',
    ];
    
    

    // Un service possède plusieurs employés
    public function employes()
    {
        return $this->hasMany(Employe::class, 'service_id', 'id_service');
    }
}
