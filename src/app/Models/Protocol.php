<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'protocol_id',
        'title',
        'status',
        'keywords',
        'pdf',
    ];
}
