<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reponses_eleves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tentative_id')->constrained('tentatives_devoir')->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');

            // QCM / Vrai-Faux
            $table->foreignId('reponse_possible_id')
                  ->nullable()
                  ->constrained('reponses_possibles')
                  ->nullOnDelete();

            // Réponse courte / Rédactionnel
            $table->text('texte_libre')->nullable();

            // Timing
            $table->integer('temps_utilise_secondes')->nullable();
            $table->boolean('temps_expire')->default(false);

            // Correction
            $table->boolean('est_correcte')->nullable();
            $table->decimal('points_obtenus', 5, 2)->default(0);
            $table->text('commentaire_enseignant')->nullable();

            $table->timestamps();

            $table->index(['tentative_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reponses_eleves');
    }
};