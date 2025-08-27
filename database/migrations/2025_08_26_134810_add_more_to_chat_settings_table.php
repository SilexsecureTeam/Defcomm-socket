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
        Schema::table('chat_settings', function (Blueprint $table) {
            $table->integer('walkie_language')->nullable();
            $table->integer('chat_language')->nullable();
            $table->integer('app_language')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_settings', function (Blueprint $table) {
            $table->dropColumn('walkie_language');
            $table->dropColumn('chat_language');
            $table->dropColumn('app_language');
        });
    }
};
