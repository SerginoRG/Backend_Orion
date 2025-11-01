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
        Schema::create('presences', function (Blueprint $table) {
            $table->id('id_presence');
            $table->date('date_presence');
            $table->time('heure_arrivee')->nullable();
            $table->time('heure_depart')->nullable();
            $table->enum('statut_presence', ['Présent', 'En retard', 'Absent'])->default('Présent');

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
        Schema::dropIfExists('presences');
    }
};
