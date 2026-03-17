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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->foreignId('product_id')->constrained();
            $table->date('date');
            $table->time('start_time');
            $table->integer('pax');
            $table->string('source'); 
            // wordpress / bokun / ota / admin
            $table->string('external_id')->nullable();
            // id de bokun o OTA
            $table->string('status')->default('confirmed');
            // confirmed / cancelled / pending
            $table->timestamps();
            $table->index(['product_id','date','start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
