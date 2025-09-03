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
        Schema::table('chat_call_logs', function (Blueprint $table) {
            $table->integer('mss_id')->nullable();
            $table->string('call_duration')->nullable();
            $table->enum('call_state',['pick','miss', 'end'])->default('miss');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_call_logs', function (Blueprint $table) {
            $table->dropColumn('mss_type');
            $table->dropColumn('call_duration');
            $table->dropColumn('call_state');
        });
    }
};
