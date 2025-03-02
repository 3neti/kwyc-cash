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
        Schema::create('cashes', function (Blueprint $table) {
            $table->id();
            $table->integer('value');
            $table->string('currency');
            $table->string('tag')->nullable();
            $table->string('secret')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('nullified_at')->nullable();
            $table->timestamp('expires_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashes');
    }
};
