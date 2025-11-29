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
        Schema::create('bounty_category_subs', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->string('label');
            $table->string('description')->nullable();
            $table->double('award_point')->nullable();
            $table->double('award_amount')->nullable();
            $table->enum("status", ['active', 'disabled'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounty_category_subs');
    }
};
