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
        Schema::create('contrats', function (Blueprint $table) {
              $table->id('id_contrat');
            $table->string('type_contrat');
            $table->date('date_debut_contrat');
            $table->date('date_fin_contrat')->nullable();
            $table->enum('statut_contrat', ['En cours', 'Terminé', 'Renouvelé'])->default('En cours');

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
        Schema::dropIfExists('contrats');
    }
};
