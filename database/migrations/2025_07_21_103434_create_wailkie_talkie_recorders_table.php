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
        Schema::create('wailkie_talkie_recorders', function (Blueprint $table) {
            $table->id();
            $table->integer('channel_id');
            $table->integer('user_id');
            $table->integer('subscriber_id');
            $table->text('record');
            $table->text('record_text')->nullable();
            $table->string('file_size')->nullable();
            $table->string('file_ext')->nullable();
            $table->string('fileSize_num')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wailkie_talkie_recorders');
    }
};
