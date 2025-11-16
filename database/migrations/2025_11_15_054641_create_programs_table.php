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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('user_type')->default('user');
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('type')->default('bounty');
            $table->enum('status', ['active', 'block'])->default('active');
            $table->dateTime("started_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
