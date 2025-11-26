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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->string('stripe_transaction_id')->nullable();
            $table->enum('type', [
                'charge',
                'refund',
                'partial_refund',
                'dispute',
                'fee'
            ]);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('eur');
            $table->enum('status', ['pending', 'succeeded', 'failed'])->default('pending');
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('payment_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
