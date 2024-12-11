<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitoreo extends Model
{
use HasFactory;

protected $table = 'monitoreo'; // Especificas el nombre de la tabla
protected $primaryKey = 'id'; // Elige la clave primaria
public $incrementing = true; // Indica que es una clave primaria autoincremental
protected $keyType = 'int'; // Tipo de la clave primaria

protected $fillable = [
'NombreProyecto',
'Sinodal1Es',
'EstatusSin1',
'FechaSinodal1',
'ObservacionSino1',
'Sinodal2Es',
'EstatusSin2',
'FechaSinodal2',
'ObservacionSino2',
'Sinodal3Es',
'EstatusSin3',
'FechaSinodal3',
'ObservacionSino3',
];
}
