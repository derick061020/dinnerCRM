<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_timeslots', function (Blueprint $table) {
            $table->id();

            // FK al producto
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Día de la semana: 0=domingo, 1=lunes, ... 6=sábado
            $table->tinyInteger('weekday');

            // Hora del slot
            $table->time('start_time');

            // Prioridad en caso de varios slots en el mismo día
            $table->integer('priority')->default(0);

            // Activo o inactivo
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_timeslots');
    }
};