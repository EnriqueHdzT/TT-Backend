<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'student_ID',
        'title_protocol',
        'staff_ID',
        'keywords',
        'protocol_doc',
    ];

    // Definir la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($protocol) {
            $protocol->user()->delete();
        });
    }
}
