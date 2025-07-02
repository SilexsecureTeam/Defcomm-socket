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
            $table->enum('statusApp',['pending','reject','approved','block'])->default('pending');
            $table->enum('statusNdpc',['pending','reject', 'verified','block'])->default('pending');
            $table->string('ndpcCode')->nullable();
            $table->string('rc_number')->nullable();
            $table->string('rc_doc')->nullable();
            $table->string('tin')->nullable();
            $table->string('tin_doc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('statusApp');
            $table->dropColumn('statusNdpc');
            $table->dropColumn('ndpcCode');
            $table->dropColumn('rc_number');
            $table->dropColumn('rc_doc');
            $table->dropColumn('tin');
            $table->dropColumn('tin_doc');
        });
    }
};
