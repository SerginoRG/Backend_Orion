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
        Schema::create('salaires', function (Blueprint $table) {
            $table->id('id_salaire');
            $table->string('mois_salaire'); 
            $table->year('annee_salaire'); 
            $table->decimal('salaire_base', 10, 2);
            $table->decimal('primes_salaire', 10, 2)->default(0);
            $table->decimal('retenues_salaire', 10, 2)->default(0);
            $table->decimal('salaire_net', 10, 2);

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
        Schema::dropIfExists('salaires');
    }
};
