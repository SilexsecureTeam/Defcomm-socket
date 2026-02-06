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
        Schema::table('event_forms', function (Blueprint $table) {
            $table->string('started_at')->nullable();
            $table->string('ended_at')->nullable();
            $table->string('location')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('timezone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_forms', function (Blueprint $table) {
            $table->dropColumn('started_at');
            $table->dropColumn('ended_at');
            $table->dropColumn('location');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('timezone');
        });
    }
};
