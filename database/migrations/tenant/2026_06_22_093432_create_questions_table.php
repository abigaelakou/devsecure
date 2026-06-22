<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devoir_id')->constrained()->onDelete('cascade');
            $table->text('enonce');
            $table->string('image')->nullable();
            $table->enum('type', ['qcm', 'vrai_faux', 'reponse_courte', 'redactionnel']);
            $table->integer('ordre')->default(0);
            $table->decimal('points', 5, 2)->default(1.00);
            $table->integer('temps_secondes')->nullable();
            $table->text('explication')->nullable();
            $table->timestamps();

            $table->index(['devoir_id', 'ordre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};