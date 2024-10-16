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
        Schema::create('hr_bitacoras', function (Blueprint $table) {
            $table->id();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable()->default(null);
            $table->string('detalles')->nullable()->default(null);
            $table->foreignId('id_trabajador')->constrained(table: 'hr_trabajadores');
            $table->foreignId('id_proyecto')->constrained(table: 'man_proyectos')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_bitacoras');
    }
};
