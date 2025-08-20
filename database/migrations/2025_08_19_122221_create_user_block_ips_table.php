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
        Schema::create('user_block_ips', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('ip_address');
            $table->string('reason')->nullable();
            $table->enum('status', ['blocked', 'unblocked'])->default('blocked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_block_ips');
    }
};
