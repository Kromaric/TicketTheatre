<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spectacles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration')->nullable(); // En minutes
            $table->decimal('base_price', 8, 2); // Prix de base
            $table->string('image_url')->nullable();
            $table->string('poster_url')->nullable(); // Affiche officielle
            $table->string('trailer_url')->nullable(); // Lien vers bande-annonce
            $table->string('language')->default('fr');
            $table->integer('age_restriction')->nullable(); // Ex: 12, 16, 18
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('director')->nullable(); // Metteur en scène
            $table->json('actors')->nullable(); // ["Jean Dupont", "Marie Martin"]
            $table->boolean('is_published')->default(false);
            $table->enum('status', ['upcoming', 'ongoing', 'finished', 'cancelled'])->default('upcoming');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spectacles');
    }
};
