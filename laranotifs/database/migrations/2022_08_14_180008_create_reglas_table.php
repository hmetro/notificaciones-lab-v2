<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reglas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->boolean('ene');
            $table->boolean('tipo_validacion');
            $table->string('x_servicio')->nullable();
            $table->string('x_origen')->nullable();
            $table->string('x_motivo')->nullable();
            $table->string('x_especialidad')->nullable();
            $table->string('x_medico')->nullable();
            $table->string('x_idprueba')->nullable();
            $table->json('add_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reglas');
    }
};
