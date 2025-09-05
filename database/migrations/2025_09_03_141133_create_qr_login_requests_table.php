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
        Schema::create('qr_login_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('code')->unique();             // the QR code payload
            $table->enum('status', ['pending', 'approved', 'expired', 'redeemed'])->default('pending');
            $table->integer('approved_user_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_login_requests');
    }
};
