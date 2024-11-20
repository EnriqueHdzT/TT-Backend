<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContenidoPrincipalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contenido_principal', function (Blueprint $table) {
            $table->id('id_contenido');
            $table->enum('tipo_contenido', ['aviso', 'tip', 'pregunta']);
            $table->string('titulo', 255)->nullable(); // Para avisos y tips
            $table->text('descripcion')->nullable(); // Para avisos y tips
            $table->string('url_imagen', 500)->nullable(); // Solo para avisos
            $table->string('url_pagina', 500)->nullable(); // Solo para tips
            $table->text('pregunta')->nullable(); // Solo para preguntas
            $table->text('respuesta')->nullable(); // Solo para preguntas
            $table->date('fecha'); // Fecha general para todos los tipos
            $table->timestamps(); // Agrega columnas created_at y updated_at
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

