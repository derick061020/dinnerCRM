<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kitchen_purchases', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('supplier')->nullable();
            $table->string('item');            // Carne, Salmón, Pollo, Vegetariano, Vegano...
            $table->integer('portions');       // 1 porción = 1 plato
            $table->decimal('cost_total', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['date', 'item']);
        });

        // Conteo físico semanal para la conciliación (merma)
        Schema::create('kitchen_counts', function (Blueprint $table) {
            $table->id();
            $table->date('week_start');
            $table->string('item');
            $table->integer('physical_count')->default(0);
            $table->timestamps();

            $table->unique(['week_start', 'item']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kitchen_counts');
        Schema::dropIfExists('kitchen_purchases');
    }
};
