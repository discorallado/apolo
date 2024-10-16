<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManProyectosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users') && Schema::hasTable('man_clientes')) {
            Schema::create('man_proyectos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_cliente')->constrained(table: 'man_clientes');
                $table->string('titulo');
                $table->text('detalle')->nullable()->default(null);
                $table->integer('monto_proyectado')->nullable()->default(null);
                $table->integer('monto_final')->nullable()->default(null);
                $table->timestamp('closed_at')->nullable()->default(null);
                $table->boolean('estado')->default(false);
                $table->foreignId('user_id')->constrained(table: 'man_clientes');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('man_proyectos');
    }
}
