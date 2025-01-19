<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    protected $hidden = [
        'laravel_through_key',
        'created_at',
        'updated_at',
    ];

    // Definir la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function protocolRoles()
    {
        return $this->hasMany(ProtocolRole::class, 'user_id')
            ->where('role', 'student');
    }

    public function protocols()
    {
        return $this->belongsToMany(Protocol::class, 'protocol_roles', 'user_id', 'protocol_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($student) {
            $student->load('user');
            $backupData = array_merge(
                $student->toArray(),
                ['email' => $student->user?->email]
            );
            DB::table('protocol_roles')
                ->where('user_id', $student->id)
                ->where('role', 'student')
                ->update(['person_data_backup' => json_encode($backupData)]);

            $student->user()->delete();
        });
    }
}
