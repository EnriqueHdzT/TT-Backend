<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lastname',
        'second_lastname',
        'name',
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

    public function directors()
    {
        return $this->hasMany(Protocol::class, 'director1_id')->orWhere('director2_id', $this->id);
    }

    public function sinodales()
    {
        return $this->hasMany(Protocol::class, 'sinodal1_id')->orWhere('sinodal2_id', $this->id)->orWhere('sinodal3_id', $this->id);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($staff) {
            DB::table('protocols')->where('director1_id', $staff->id)
                ->update(['director1_data' => json_encode($staff->toArray())]);
            DB::table('protocols')->where('director2_id', $staff->id)
                ->update(['director2_data' => json_encode($staff->toArray())]);
            DB::table('protocols')->where('sinodal1_id', $staff->id)
                ->update(['sinodal1_data' => json_encode($staff->toArray())]);
            DB::table('protocols')->where('sinodal2_id', $staff->id)
                ->update(['sinodal2_data' => json_encode($staff->toArray())]);
            DB::table('protocols')->where('sinodal3_id', $staff->id)
                ->update(['sinodal3_data' => json_encode($staff->toArray())]);

            $staff->user()->delete();
        });
    }
}
