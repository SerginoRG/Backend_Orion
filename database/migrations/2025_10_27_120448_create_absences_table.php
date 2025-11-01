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
        Schema::create('absences', function (Blueprint $table) {
        $table->id('id_absence');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('motif_absence');
            $table->enum('statut_absence', ['En attente', 'Validée', 'Refusée'])->default('En attente');
            $table->string('justificatif')->nullable();

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
        Schema::dropIfExists('absences');
    }
};
