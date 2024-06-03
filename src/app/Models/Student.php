<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'lastname',
        'second_lastname',
        'name',
        'student_id',
        'career',
        'curriculum',
        'altern_email',
        'phone_number',
    ];

    // Definir la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($student) {
            $student->user()->delete();
        });
    }
}
