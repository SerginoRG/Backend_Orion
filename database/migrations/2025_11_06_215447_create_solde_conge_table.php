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
        Schema::create('solde_conge', function (Blueprint $table) {
            $table->id('id_solde');
            $table->integer('annee'); // ex: 2025
            $table->integer('jours_acquis')->default(30); // toujours 30 par défaut
            $table->integer('jours_consommes')->default(0);
            $table->integer('jours_restants')->default(30);
            
           // Clé étrangère vers employes
            $table->unsignedBigInteger('employe_id');
            $table->foreign('employe_id')
                  ->references('id_employe')
                  ->on('employes')
                  ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solde_conge');
    }
};
