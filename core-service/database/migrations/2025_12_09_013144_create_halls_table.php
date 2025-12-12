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
        Schema::create('halls', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->integer('capacity')->unsigned();
            $table->text('description')->nullable();
            $table->string('type')->nullable(); // Ex: "Grande salle", "Petit théâtre"
            $table->boolean('is_active')->default(true);
            $table->string('image_url')->nullable();
            $table->json('amenities')->nullable(); // ["Climatisation", "Bar", "Accessibilité PMR"]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halls');
    }
};
