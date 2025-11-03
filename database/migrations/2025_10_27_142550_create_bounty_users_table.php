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
        Schema::create('bounty_users', function (Blueprint $table) {
            $table->id();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('otp')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('timezone')->nullable();
            $table->string('photo')->nullable();
            $table->text('bio')->nullable();
            $table->enum('user_type',['user','group','company'])->default('user');
            $table->enum('status',['active', 'pending', 'block'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounty_users');
    }
};
