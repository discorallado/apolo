<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManClientesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::create('man_clientes', function (Blueprint $table) {
                $table->id();
                $table->string('rut')->nullable()->default(null);
                $table->string('nombre');
                $table->text('direccion')->nullable()->default(null);
                $table->string('ciudad')->nullable()->default(null);
                $table->string('telefono')->nullable()->default(null);
                $table->text('giro')->nullable()->default(null);
                $table->foreignId('user_id')->constrained(table:'users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('man_clientes');
    }
}
