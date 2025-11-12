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
        Schema::create('bounty_user_transanctions', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
            $table->integer('user_id');
            $table->double('amount');
            $table->enum('status', ['pending', 'complete', 'cancel'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounty_user_transanctions');
    }
};
