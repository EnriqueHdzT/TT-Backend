<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProtocolStatus;

class Protocol extends Model
{
    use HasFactory;
    use HasUuids;

    protected $keyType = 'string';

    protected $fillable = [
        'protocol_id',
        'title',
        'resume',
        'period',
        'current_status',
        'keywords',
        'pdf',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'pdf',
        'pivot'
    ];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function students()
    {
        return $this->hasMany(ProtocolRole::class, 'protocol_id')->where('role', 'student');
    }

    public function directors()
    {
        return $this->hasMany(ProtocolRole::class, 'protocol_id')->where('role', 'director');
    }

    public function sinodals()
    {
        return $this->hasMany(ProtocolRole::class, 'protocol_id')->where('role', 'sinodal');
    }

    public function datesAndTerms()
    {
        return $this->belongsTo(DatesAndTerms::class, 'period');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(ProtocolStatus::class);
    }

    public function status()
    {
        return $this->hasOne(ProtocolStatus::class, 'protocol_id');
    }
}
