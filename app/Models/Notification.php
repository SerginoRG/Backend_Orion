<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
   protected $primaryKey = 'id_notification';
    protected $fillable = ['employe_id', 'titre', 'message', 'is_read'];
}
