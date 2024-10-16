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
        Schema::create('hr_pagos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_bitacora');
            $table->timestamp('fecha');
            $table->bigInteger('id_trabajador');
            $table->boolean('tipo'); // 0:cobro dia 1:pago día
            $table->integer('monto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_pagos');
    }
};
