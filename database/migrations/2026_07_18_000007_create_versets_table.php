<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('versets', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 100);
            $table->string('livre', 50);
            $table->integer('chapitre');
            $table->integer('verset');
            $table->text('texte');
            $table->string('version', 20)->default('LSG');
            $table->timestamps();

            $table->unique(['livre', 'chapitre', 'verset', 'version'], 'unique_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('versets');
    }
};
