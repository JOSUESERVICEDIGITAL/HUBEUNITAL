<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('volunteer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('titre_principal')->default('Devenir Bénévole');
            $table->string('placeholder_nom')->default('Nom');
            $table->string('placeholder_prenom')->default('Prénom');
            $table->string('placeholder_ville')->default('Ville actuelle');
            $table->string('placeholder_telephone')->default('Téléphone');
            $table->string('placeholder_nationalite')->default('Nationalité');
            $table->string('placeholder_annee_arrivee')->default('Ann?e d?arriv?e au Maroc');
            $table->string('placeholder_email')->default('Adresse email');
            $table->string('texte_bouton')->default('Envoyer ma demande');
            // Texte trop long, donc on ne met pas de default()
            $table->text('message_confirmation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('volunteer_settings');
    }
};
