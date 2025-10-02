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
        Schema::create('helpdesk_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number');
            $table->string('name');
            $table->string('nip');
            $table->string('phone');
            $table->string('description');
            $table->string('pic')->nullable();
            $table->string('message_id')->nullable();
            $table->string('status')->default('in');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_forms');
    }
};
