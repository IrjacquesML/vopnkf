<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traductions', function (Blueprint $table) {
            $table->id();
            $table->enum('type_contenu', ['lecon', 'categorie', 'question', 'interface']);
            $table->unsignedBigInteger('contenu_id')->nullable();
            $table->string('cle_texte', 100)->nullable();
            $table->text('texte_original');
            $table->string('langue', 10);
            $table->text('texte_traduit');
            $table->timestamps();

            $table->index(['type_contenu', 'contenu_id', 'langue'], 'idx_type_contenu');
            $table->index(['cle_texte', 'langue'], 'idx_cle_langue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traductions');
    }
};
