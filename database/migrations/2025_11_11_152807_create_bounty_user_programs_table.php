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
        Schema::create('bounty_user_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('detail')->nullable();
            $table->enum('status', ['active', 'block'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounty_user_programs');
    }
};
