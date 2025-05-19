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
        Schema::create('personal_access_token_hits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_access_token_id')->constrained('personal_access_tokens')->onDelete('cascade');
            $table->date('hit_date');
            $table->unsignedInteger('hit_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_token_hits');
    }
};
