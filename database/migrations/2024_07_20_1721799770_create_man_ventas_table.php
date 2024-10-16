<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManVentasTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('man_clientes') && Schema::hasTable('man_proyectos') && Schema::hasTable('man_movimientos')) {
            Schema::create('man_ventas', function (Blueprint $table) {
                $table->id();
                $table->string('folio');
                $table->date('fecha_dcto')->nullable()->default(null);
                $table->string('tipo_doc');
                $table->foreignId('id_cliente')->constrained(table: 'man_clientes');
                $table->foreignId('id_proyecto')->constrained(table: 'man_proyectos')->nullable()->default(null);
                $table->foreignId('id_movimiento')->constrained(table: 'man_movimientos')->nullable()->default(null);
                $table->integer('excento')->nullable()->default(null);
                $table->integer('neto');
                $table->integer('iva')->nullable()->default(null);
                $table->integer('total');
                $table->text('observaciones')->nullable()->default(null);
                $table->foreignId('user_id')->constrained(table: 'users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('man_ventas');
    }
}
