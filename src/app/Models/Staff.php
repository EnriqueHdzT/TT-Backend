<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_image',
        'lastname',
        'second_lastname',
        'name',
        'birth_date',
        'gender',
        'staff_id',
        'precedence',
        'academy',
        'altern_email',
        'phone_number',
    ];

    // Definir la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function protocolsDirectors()
    {
        return $this->belongsToMany(Protocol::class, 'protocol_directors')->withTimestamps();
    }

    public function protocolsSinodales()
    {
        return $this->belongsToMany(Protocol::class, 'protocol_sinodales')->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($staff) {
            $staff->user()->delete();
        });
    }
}
