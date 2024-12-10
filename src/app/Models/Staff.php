<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Staff extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'lastname',
        'second_lastname',
        'name',
        'precedence',
        'altern_email',
        'phone_number',
        'staff_type',
    ];

    // Definir la relación con el modelo User
    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function protocolRoles()
    {
        return $this->hasMany(ProtocolRole::class, 'user_id')
            ->whereIn('role', ['director', 'sinodal']);
    }

    public function academies()
    {
        return $this->belongsToMany(Academy::class, 'staff_academy');
    }


    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($staff) {
            $staff->load('user');
            $backupData = array_merge(
                $staff->toArray(),
                ['email' => $staff->user?->email]
            );
            DB::table('protocol_roles')
                ->where('person_id', $staff->id)
                ->whereIn('role', ['director', 'sinodal'])
                ->update(['person_data_backup' => json_encode($backupData)]);

            $staff->user()->delete();
        });
    }
}
