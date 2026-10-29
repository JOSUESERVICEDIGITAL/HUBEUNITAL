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
        Schema::create('donation_settings', function (Blueprint $table) {
              $table->id();
            $table->string('titre')->default('Faire un Don');
            $table->text('texte_introduction')->nullable();
            $table->string('nom_banque')->nullable();
            $table->string('rib')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift')->nullable();
            $table->text('informations_complementaires')->nullable();
            $table->text('instructions_apres_don')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_settings');
    }
};
