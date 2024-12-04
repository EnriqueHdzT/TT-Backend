<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContenidoPrincipal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contenido_principal', function (Blueprint $table) {
            $table->id('id_contenido'); // SERIAL equivale a BIGINT UNSIGNED AUTO_INCREMENT
            $table->string('tipo_contenido', 10);
            $table->string('titulo', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('url_imagen', 2083)->nullable();
            $table->string('url_pagina', 2083)->nullable();
            $table->text('pregunta')->nullable();
            $table->text('respuesta')->nullable();
            $table->date('fecha');
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contenido_principal');
    }
}
