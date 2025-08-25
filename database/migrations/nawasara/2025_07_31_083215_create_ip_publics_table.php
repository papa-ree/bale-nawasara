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
        Schema::create('ip_publics', function (Blueprint $table) {
            $table->string('id')->primary()->comment('id by mikrotik, format: .id');
            $table->string('address')->nullable();
            $table->string('interface')->nullable();
            $table->string('published')->nullable();
            $table->string('invalid')->nullable();
            $table->string('dhcp')->comment('format: DHCP')->nullable();
            $table->string('dynamic')->nullable();
            $table->string('complete')->nullable();
            $table->string('disabled')->nullable();
            $table->string('comment')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('gateway')->nullable();
            $table->string('subnet')->nullable();
            $table->string('network')->nullable();
            $table->string('subnet_mask')->nullable();
            $table->uuid('pic_contact_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ip_publics');
    }
};
