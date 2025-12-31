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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->nullable();
            $table->string('label')->nullable();
            $table->string('short_message')->nullable();
            $table->string('body_message')->nullable();
            $table->string('icon')->nullable();
            $table->dateTime('expire')->nullable();  
            $table->enum('source',['super','admin'])->default('admin'); 
            $table->enum('status',['pending','active'])->default('pending'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
