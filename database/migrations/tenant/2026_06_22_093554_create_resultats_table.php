<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tentative_id')->constrained('tentatives_devoir')->onDelete('cascade');
            $table->foreignId('eleve_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('devoir_id')->constrained()->onDelete('cascade');

            $table->decimal('note_finale', 5, 2);
            $table->decimal('note_sur', 5, 2);
            $table->decimal('pourcentage', 5, 2);
            $table->integer('rang')->nullable();

            $table->integer('bonnes_reponses')->default(0);
            $table->integer('mauvaises_reponses')->default(0);
            $table->integer('sans_reponse')->default(0);
            $table->integer('total_questions');

            $table->boolean('fraude_detectee')->default(false);
            $table->integer('nb_evenements_antitriche')->default(0);

            $table->timestamps();

            $table->index(['devoir_id', 'eleve_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultats');
    }
};