<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solde_conge', function (Blueprint $table) {
            $table->integer('jours_permission_max')->default(3);
            $table->integer('jours_permission_utilises')->default(0);
            $table->integer('jours_permission_restants')->default(3);
        });
    }

    public function down(): void
    {
        Schema::table('solde_conge', function (Blueprint $table) {
            $table->dropColumn(['jours_permission_max', 'jours_permission_utilises', 'jours_permission_restants']);
        });
    }
};
