<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenements_antitriche', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tentative_id')->constrained('tentatives_devoir')->onDelete('cascade');
            $table->foreignId('eleve_id')->constrained('users')->onDelete('cascade');

            $table->enum('type', [
                'changement_onglet',
                'fenetre_reduite',
                'quitter_navigateur',
                'copier_coller',
                'clic_droit',
                'touche_impression_ecran',
                'plein_ecran_quitte',
                'focus_perdu',
                'focus_retour',
                'soumission_auto',
            ]);

            $table->integer('numero_question')->nullable();
            $table->integer('compteur_type')->default(1);
            $table->text('details')->nullable();
            $table->string('adresse_ip')->nullable();
            $table->timestamp('survenu_le');
            $table->timestamps();

            $table->index(['tentative_id', 'type']);
            $table->index('survenu_le');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenements_antitriche');
    }
};