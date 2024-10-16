<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManMovimientosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('man_proyectos') && Schema::hasTable('users')) {
            Schema::create('man_movimientos', function (Blueprint $table) {
                $table->id();
                $table->string('tipo');
                $table->text('detalle');
                $table->date('fecha')->default(now());
                $table->string('cot')->nullable()->default(null);
                $table->integer('monto_proyecto')->nullable()->default(null);
                $table->string('factura')->nullable()->default(null);
                $table->foreignId('id_proyecto')->constrained(table: 'man_proyectos')->nullable()->default(null);
                $table->integer('cargo')->nullable()->default(null);
                $table->integer('ingreso')->nullable()->default(null);
                $table->text('observaciones')->nullable()->default(null);
                $table->boolean('estado')->default(false);
                $table->foreignId('user_id')->constrained(table: 'users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('man_movimientos');
    }
}
