<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'title',
        'status',
        'keywords',
        'pdf',
    ];
}
