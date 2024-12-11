<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContenidoPrincipal extends Model
{
    use HasFactory;

    protected $table = 'contenido_principal';
    protected $primaryKey = 'id_contenido';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'tipo_contenido',
        'titulo',
        'descripcion',
        'url_imagen',
        'url_pagina',
        'pregunta',
        'respuesta',
        'fecha'
    ];
}