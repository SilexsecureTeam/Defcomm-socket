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
        Schema::create('app_stores', function (Blueprint $table) {
            $table->id();
            $table->integer('user');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_opt')->nullable();
            $table->string('os')->nullable();
            $table->string('app_icon')->nullable();
            $table->string('feature_image')->nullable();
            $table->text('policy')->nullable();
            $table->text('app_bundle')->nullable();
            $table->string('name_release')->nullable();
            $table->string('version')->nullable();
            $table->text('copyright')->nullable();
            $table->enum('release',['manual', 'automatic', 'automatic_earlier'])->default('manual');
            $table->enum('collect_data',['yes', 'no'])->default('yes');
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_address')->nullable();
            $table->string('contact_other')->nullable();
            $table->string('location_precise')->nullable(); 
            $table->string('location_coarse')->nullable(); 
            $table->string('sensitive_info')->nullable(); 
            $table->string('app_id')->nullable(); 
            $table->string('app_id_name')->nullable(); 
            $table->string('app_id_prefix')->nullable(); 
            $table->string('app_id_surfix')->nullable();
            $table->enum('status', ['pending', 'reject', 'approved', 'disable', 'active'])->default('pending');
            $table->dateTime('active_date')->nullable();
            $table->dateTime('disable_date')->nullable();
            $table->dateTime('reject_date')->nullable();
            $table->dateTime('resubmit_date')->nullable();
            $table->text('comment')->nullable();
            $table->text('rc_number')->nullable();
            $table->text('tin_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_stores');
    }
};
