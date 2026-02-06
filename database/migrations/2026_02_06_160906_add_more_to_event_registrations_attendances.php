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
        Schema::table('event_registrations_attendances', function (Blueprint $table) {
            $table->json('certificate')->nullable(); // {label:##, path:##, status:approve|pending|collected}
            $table->json('souvenir')->nullable(); // {label:##, path:##, status:approve|pending|collected}
            $table->string('clockin')->nullable();
            $table->string('clockout')->nullable();
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
        Schema::table('event_registrations_attendances', function (Blueprint $table) {
            $table->dropColumn('certificate');
            $table->dropColumn('souvenir');
            $table->dropColumn('clockin');
            $table->dropColumn('clockout');
            $table->dropColumn('location');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('timezone');
        });
    }
};
