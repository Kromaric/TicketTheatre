<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration de test UNIQUEMENT — reproduit le schéma partagé du core-service.
 * Cette migration n'existe pas en production : la BD est gérée par le core-service.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 : table de base identique à core-service (create_users_table)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('user');
            $table->string('phone_number')->nullable();
            $table->string('sex', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Étape 2 : alter identique à update_users_table du core-service
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('avatar')->nullable()->after('date_of_birth');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->json('preferences')->nullable()->after('is_active');
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
