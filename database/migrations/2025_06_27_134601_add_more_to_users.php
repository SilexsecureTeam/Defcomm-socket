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
            $table->string('app_role')->nullable()->comment('developer', 'admin', 'manager');
            $table->double('number_app')->nullable();
            $table->double('number_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('app_role');
            $table->dropColumn('number_app');
            $table->dropColumn('number_user');
        });
    }
};
