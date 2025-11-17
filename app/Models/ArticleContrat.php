<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleContrat extends Model
{
    protected $table = 'articles_contrat';
    protected $primaryKey = 'id_article';
    public $timestamps = false;
    
    protected $fillable = [
        'article',
        'titre',
        'contenu',
    ];
}
