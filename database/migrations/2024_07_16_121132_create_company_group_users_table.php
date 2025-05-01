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
        Schema::create('company_group_users', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id');
            $table->integer('group_id');
            $table->integer('user_id');
            $table->dateTime('join_date')->nullable();
            $table->enum('status',['pending','joined'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_group_users');
    }
};
