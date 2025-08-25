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
        Schema::create('ip_addresses', function (Blueprint $table) {
            $table->string('id')->primary()->comment('id by mikrotik, format: .id');
            $table->string('address');
            $table->string('network')->nullable();
            $table->string('interface')->nullable();
            $table->string('actual_interface')->nullable();
            $table->string('invalid')->nullable();
            $table->string('dynamic')->nullable();
            $table->string('disabled')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_addresses');
    }
};
