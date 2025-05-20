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
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('duration')->nullable();
            $table->integer('number_join')->nullable();
            $table->string('title')->nullable();
            $table->string('agenda')->nullable();
            $table->string('subject')->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('meeting_id')->nullable();
            $table->enum('status', ['create', 'start', 'end'])->default('create');
            $table->enum('group_user', ['user', 'group'])->default('user');
            $table->integer('group_user_id')->nullable();
            $table->dateTime('startdatetime')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
