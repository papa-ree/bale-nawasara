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
        Schema::create('kuma_monitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dns_record_id')->nullable();
            $table->string('ip_public_id')->nullable();
            $table->string('kuma_id')->comment('format: id')->nullable();
            $table->boolean('kuma_synced')->default(0);
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('pathName')->nullable();
            $table->string('url')->unique()->nullable();
            $table->string('method')->nullable();
            $table->string('hostname')->unique()->nullable();
            $table->string('port')->nullable();
            $table->boolean('active')->default(1)->nullable();
            $table->string('type');
            $table->integer('timeout')->default(48);
            $table->integer('interval')->default(60);
            $table->integer('retry_interval')->default(60);
            $table->integer('resend_interval')->default(0);
            $table->boolean('expiry_notification')->default(0);
            $table->json('tags')->nullable();
            $table->json('notification_id_list')->nullable();
            $table->boolean('uptime_check_enabled')->default(1);
            $table->boolean('uptime_status')->nullable();
            $table->text('uptime_check_failure_reason')->nullable();
            $table->integer('uptime_check_failure_duration')->nullable();
            $table->timestamp('certificate_expiration_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuma_monitors');
    }
};
