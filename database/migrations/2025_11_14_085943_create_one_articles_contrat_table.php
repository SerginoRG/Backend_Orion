<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles_contrat', function (Blueprint $table) {
            $table->id('id_article');
            $table->string('article');      // Exemple : "Article 1"
            $table->string('titre');        // Exemple : "Objet du contrat"
            $table->longText('contenu');    // Le contenu complet de l’article
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles_contrat');
    }
};
