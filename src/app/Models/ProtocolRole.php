<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProtocolRole extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'protocol_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'protocol_id',
        'user_id',
        'role',
        'person_data_backup',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'person_data_backup' => 'array',
    ];

    public function protocol()
    {
        return $this->belongsTo(Protocol::class, 'protocol_id');
    }

    public function person()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
