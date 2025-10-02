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
        Schema::create('dns_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type');
            $table->json('content');
            $table->boolean('proxiable');
            $table->boolean('proxied');
            $table->string('ttl');
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->string('comment')->nullable();
            $table->json('tags')->nullable();
            $table->string('created_on')->nullable();
            $table->string('modified_on')->nullable();
            $table->string('comment_modified_on')->nullable();
            $table->string('tags_modified_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dns_records');
    }
};
