<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            // Supprimer la colonne taux_horaire_employe si elle existe
            if (Schema::hasColumn('employes', 'taux_horaire_employe')) {
                $table->dropColumn('taux_horaire_employe');
            }

            // Ajouter la colonne salaire_base_employe
            if (!Schema::hasColumn('employes', 'salaire_base_employe')) {
                $table->decimal('salaire_base_employe', 10, 2)
                      ->default(0)
                      ->after('poste_employe');
            }
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table) {
            // Supprimer salaire_base_employe si on revient en arrière
            if (Schema::hasColumn('employes', 'salaire_base_employe')) {
                $table->dropColumn('salaire_base_employe');
            }

            // Remettre taux_horaire_employe
            if (!Schema::hasColumn('employes', 'taux_horaire_employe')) {
                $table->decimal('taux_horaire_employe', 10, 2)
                      ->default(0)
                      ->after('poste_employe');
            }
        });
    }
};
