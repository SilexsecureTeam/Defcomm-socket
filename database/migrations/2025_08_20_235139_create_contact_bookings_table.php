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
        Schema::create('contact_bookings', function (Blueprint $table) {
            $table->id();
            $table->dateTime("dateTime")->nullable();
            $table->string("name")->nullable();
            $table->string("email")->nullable();
            $table->string("role")->nullable();
            $table->string("location")->nullable();
            $table->string("phone")->nullable();
            $table->string("meeting_type")->nullable();
            $table->string("reason")->nullable();
            $table->string("req")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_bookings');
    }
};
