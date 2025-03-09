<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use FrittenKeeZ\Vouchers\Config;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(Config::table('vouchers'), function (Blueprint $table) {
            $table->longText('metadata')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(Config::table('vouchers'), function (Blueprint $table) {
            $table->text('metadata')->nullable()->change();
        });
    }
};
