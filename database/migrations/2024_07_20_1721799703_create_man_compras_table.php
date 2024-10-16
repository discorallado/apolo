<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManComprasTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users')&&Schema::hasTable('man_proyectos')&&Schema::hasTable('man_clientes')) {
            Schema::create('man_compras', function (Blueprint $table) {
                $table->id();
                $table->string('folio');
                $table->date('fecha_dcto');
                $table->string('tipo_doc');
                $table->foreignId('id_proveedor')->constrained(table: 'man_proveedores');
                $table->foreignId('id_proyecto')->constrained(table: 'man_proyectos')->nullable()->default(null);
                $table->foreignId('id_cliente')->constrained(table: 'man_clientes')->nullable()->default(null);
                $table->integer('neto');
                $table->integer('iva');
                $table->integer('total');
                $table->string('centro_costo')->nullable()->default(null);
                $table->string('forma_pago')->nullable()->default(null);
                $table->text('observaciones')->nullable()->default(null);
                $table->foreignId('user_id')->constrained(table: 'users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('man_compras');
    }
}
