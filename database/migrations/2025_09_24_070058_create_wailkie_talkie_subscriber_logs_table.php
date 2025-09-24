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
        Schema::create('wailkie_talkie_subscriber_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('subscriber_id');
            $table->integer('channel_id');
            $table->dateTime('leave_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wailkie_talkie_subscriber_logs');
    }
};
