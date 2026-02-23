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
        Schema::create('certificate_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_id');
            $table->unsignedBigInteger('event_registration_id');
            $table->boolean('is_collected')->default(false);
            $table->boolean('is_sent')->default(false);
            $table->timestamps();

            $table->foreign('certificate_id')->references('id')->on('certificates')->onDelete('cascade');
            $table->foreign('event_registration_id')->references('id')->on('event_registrations')->onDelete('cascade');
        });

        Schema::create('souvenir_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('souvenir_id');
            $table->unsignedBigInteger('event_registration_id');
            $table->boolean('is_collected')->default(false);
            $table->timestamps();

            $table->foreign('souvenir_id')->references('id')->on('souvenirs')->onDelete('cascade');
            $table->foreign('event_registration_id')->references('id')->on('event_registrations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_registrations');
        Schema::dropIfExists('souvenir_registrations');
    }
};
