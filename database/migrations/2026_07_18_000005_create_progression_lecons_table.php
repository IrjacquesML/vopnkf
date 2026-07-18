<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progression_lecons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lecon_id')->constrained('lecons')->cascadeOnDelete();
            $table->enum('statut', ['non_commence', 'en_cours', 'termine'])->default('non_commence');
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_completion')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'lecon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progression_lecons');
    }
};
