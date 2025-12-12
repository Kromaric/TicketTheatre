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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seance_id')->constrained()->cascadeOnDelete();
            $table->string('booking_reference')->unique();
            $table->json('seats')->nullable(); // null = placement libre, ["A12", "A13"] = places assignées
            $table->unsignedInteger('quantity'); // Nombre de places
            $table->decimal('total_price', 8, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->string('payment_id')->nullable(); // ID du paiement (référence externe vers payment-service)
            $table->dateTime('expires_at')->nullable(); // Date d'expiration si non payé
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['user_id', 'status']);
            $table->index(['seance_id', 'status']);
            $table->index('booking_reference');
            $table->index('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
