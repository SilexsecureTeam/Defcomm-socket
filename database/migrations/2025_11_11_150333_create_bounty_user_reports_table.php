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
        Schema::create('bounty_user_reports', function (Blueprint $table) {
            $table->id();
            $table->string('ref');
            $table->integer('user_id');
            $table->integer('program_id')->nullable();
            $table->string('title')->nullable();
            $table->text('detail')->nullable();
            $table->text('admin_comment')->nullable();
            $table->json('attachment')->nullable();
            $table->double('score')->nullable();
            $table->string('category')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('status', ['new', 'review', 'accept', 'reject', 'fix', 'close'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounty_user_reports');
    }
};
