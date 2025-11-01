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
        Schema::create('bulletins', function (Blueprint $table) {
            $table->id('id_bulletin');
            $table->string('reference_bulletin')->unique(); // Code unique du bulletin
            $table->date('date_generation');
            $table->string('fichier_pdf')->nullable();

            // Clé étrangère vers salaires
            $table->unsignedBigInteger('salaire_id');
            $table->foreign('salaire_id')
                ->references('id_salaire')
                ->on('salaires')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletins');
    }
};
