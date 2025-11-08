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
        Schema::table('salaires', function (Blueprint $table) {

            $table->decimal('cnaps', 10, 2)->after('retenues_salaire')->default(0);
            $table->decimal('medical', 10, 2)->after('cnaps')->default(0);
            $table->decimal('irsa', 10, 2)->after('medical')->default(0); 

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaires', function (Blueprint $table) {
            $table->dropColumn(['cnaps', 'medical', 'irsa']);
        });
    }
};
