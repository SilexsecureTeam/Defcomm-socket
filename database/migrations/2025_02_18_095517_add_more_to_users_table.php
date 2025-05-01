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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable();
            $table->string('recover_mail')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_token')->nullable();
            $table->longText('pin')->nullable();
            $table->string('onboarding_stage')->default('new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
            $table->dropColumn('recover_mail');
            $table->dropColumn('device_type');
            $table->dropColumn('device_token');
            $table->dropColumn('pin');
            $table->dropColumn('onboarding_stage');
        });
    }
};
