<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolAcademy extends Model
{
    use HasFactory;

    // Nombre de la tabla asociada
    protected $table = 'protocol_academy';

    // Permitimos la asignación masiva en los siguientes campos
    protected $fillable = [
        'protocol_id',
        'academy_id',
    ];

    // Relación con el modelo Protocol
    public function protocol()
    {
        return $this->belongsTo(Protocol::class, 'protocol_id');
    }

    // Relación con el modelo Academy
    public function academy()
    {
        return $this->belongsTo(Academy::class, 'academy_id');
    }
}
