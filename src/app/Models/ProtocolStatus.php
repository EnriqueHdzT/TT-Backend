<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'previous_status',
        'new_status',
        'comment',
        'changed_at',
    ];

    // Relationship to the protocol
    public function protocol()
    {
        return $this->belongsTo(Protocol::class);
    }
}
