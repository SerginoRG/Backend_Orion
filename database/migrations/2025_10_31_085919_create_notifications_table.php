<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('notifications', function (Blueprint $table) {
        $table->id('id_notification');
        $table->unsignedBigInteger('employe_id');
        $table->string('titre');
        $table->string('message');
        $table->boolean('is_read')->default(false);
        $table->timestamps();

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
        Schema::dropIfExists('notifications');
    }
};
