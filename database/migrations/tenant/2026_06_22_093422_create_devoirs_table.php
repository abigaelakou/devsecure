<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoirs', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->foreignId('enseignant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('matiere_id')->constrained()->onDelete('cascade');
            $table->foreignId('classe_id')->constrained()->onDelete('cascade');
            $table->foreignId('annee_scolaire_id')->constrained('annees_scolaires')->onDelete('cascade');

            // Disponibilité
            $table->timestamp('disponible_le')->nullable();
            $table->timestamp('expire_le')->nullable();

            // Timer
            $table->integer('duree_totale_minutes')->nullable();
            $table->integer('temps_par_question_secondes')->nullable();

            // Antitriche
            $table->integer('max_changements_onglet')->default(3);
            $table->boolean('soumettre_auto_sortie')->default(true);

            // Options affichage
            $table->boolean('questions_aleatoires')->default(false);
            $table->boolean('reponses_aleatoires')->default(false);
            $table->integer('max_tentatives')->default(1);

            // Notation
            $table->decimal('note_sur', 5, 2)->default(20.00);
            $table->boolean('correction_auto')->default(true);

            $table->enum('statut', ['brouillon', 'actif', 'archive'])->default('brouillon');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['classe_id', 'statut']);
            $table->index(['enseignant_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoirs');
    }
};