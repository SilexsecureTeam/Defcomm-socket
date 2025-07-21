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
        Schema::create('wailkie_talkie_subscribers', function (Blueprint $table) {
            $table->id();
            $table->integer('channel_id');
            $table->integer('user_id');
            $table->enum('user_type', ['user', 'creator'])->default('user');
            $table->enum('status', ['pending', 'active', 'reject', 'block'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wailkie_talkie_subscribers');
    }
};
