<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
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
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function protocols()
    {
        return $this->hasMany(Protocol::class, 'student1_id')->orWhere('student2_id', $this->id)
            ->orWhere('student3_id', $this->id)->orWhere('student4_id', $this->id);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($student) {
            DB::table('protocols')->where('student1_id', $student->id)
                ->update(['student1_data' => json_encode($student->toArray())]);
            DB::table('protocols')->where('student2_id', $student->id)
                ->update(['student2_data' => json_encode($student->toArray())]);
            DB::table('protocols')->where('student3_id', $student->id)
                ->update(['student3_data' => json_encode($student->toArray())]);
            DB::table('protocols')->where('student4_id', $student->id)
                ->update(['student4_data' => json_encode($student->toArray())]);
            $student->user()->delete();
        });
    }
}
