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
        Schema::create('chat_call_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('send_user_id')->nullable();
            $table->integer('recieve_user_id')->nullable();
            $table->integer('call_st')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_call_logs');
    }
};
