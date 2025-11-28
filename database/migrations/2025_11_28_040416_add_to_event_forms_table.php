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
            $table->enum("attendance", ['enabled', 'disabled'])->default('disabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_forms', function (Blueprint $table) {
            $table->dropColumn('attendance');
        });
    }
};
