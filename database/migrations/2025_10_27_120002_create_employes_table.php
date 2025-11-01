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
        Schema::create('employes', function (Blueprint $table) {
            $table->id('id_employe');
            $table->string('matricule_employe')->unique();
            $table->string('nom_employe');
            $table->string('prenom_employe');
            $table->date('date_naissance_employe');
            $table->string('cin_employe', 12)->unique();
            $table->string('adresse_employe');
            $table->string('email_employe')->unique();
            $table->string('telephone_employe');
            $table->date('date_embauche_employe');
            $table->string('poste_employe');
            $table->decimal('salaire_base_employe', 10, 2);
            $table->string('photo_profil_employe')->nullable();

            // Clé étrangère vers services
            $table->unsignedBigInteger('service_id');
            
            $table->foreign('service_id')
                  ->references('id_service')
                  ->on('services')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
