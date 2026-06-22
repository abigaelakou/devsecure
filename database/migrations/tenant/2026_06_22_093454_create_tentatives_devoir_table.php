<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentatives_devoir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devoir_id')->constrained()->onDelete('cascade');
            $table->foreignId('eleve_id')->constrained('users')->onDelete('cascade');

            // Timing
            $table->timestamp('debut_le')->nullable();
            $table->timestamp('fin_le')->nullable();
            $table->integer('duree_reelle_secondes')->nullable();

            // Progression
            $table->integer('question_courante')->default(1);
            $table->enum('statut', ['en_cours', 'soumis', 'expire', 'abandonne'])->default('en_cours');

            // Session
            $table->string('adresse_ip')->nullable();
            $table->string('navigateur')->nullable();
            $table->string('user_agent')->nullable();

            // Note
            $table->decimal('note', 5, 2)->nullable();
            $table->decimal('note_sur', 5, 2)->nullable();
            $table->boolean('note_calculee')->default(false);

            $table->timestamps();

            $table->index(['devoir_id', 'statut']);
            $table->index(['eleve_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentatives_devoir');
    }
};