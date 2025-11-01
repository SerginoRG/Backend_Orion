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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('id_utilisateur');
            $table->string('nom_utilisateur')->unique();
            $table->string('password_utilisateur');
            $table->boolean('statut_utilisateur')->default(true);

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
        Schema::dropIfExists('utilisateurs');
    }
};
