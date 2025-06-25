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
        Schema::create('pic_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('contact_name');
            $table->text('contact_phone');
            $table->string('contact_phone_hash')->nullable()->index();
            $table->text('contact_nip')->nullable();
            $table->string('contact_nip_hash')->nullable()->index();
            $table->text('recovery_email_address')->nullable();
            $table->string('recovery_email_address_hash')->nullable()->index();
            $table->string('contact_job');
            $table->string('contact_office');
            $table->boolean('is_active')->default(1);
            $table->string('user_uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pic_contacts');
    }
};
