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
        Schema::create('user_otps', function (Blueprint $table) {
            $table->id();
            // $table->bigInteger('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->bigInteger('phone_no')->references('phone_no')->on('users')->onDelete('cascade');
            $table->string('otp');
            $table->string('type')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expire_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};