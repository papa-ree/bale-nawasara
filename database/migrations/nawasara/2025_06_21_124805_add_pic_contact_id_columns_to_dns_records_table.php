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
        Schema::table('dns_records', function (Blueprint $table) {
            $table->uuid('pic_contact_id')
                ->nullable()
                ->after('tags_modified_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dns_records', function (Blueprint $table) {
            $table->dropColumn([
                'pic_contact_id',
            ]);
        });
    }
};
