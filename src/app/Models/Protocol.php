<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    use HasFactory;

    protected $fillable = [
        'protocol_id',
        'title',
        'status',
        'students_data',
        'directors_data',
        'synods_data',
        'keywords',
        'pdf',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'protocol_students')->withTimestamps()->limit(4);
    }

    public function directors()
    {
        return $this->belongsToMany(Staff::class, 'protocol_directors')->withTimestamps()->limit(2);
    }

    public function sinodales()
    {
        return $this->belongsToMany(Staff::class, 'protocol_sinodales')->withTimestamps()->limit(3);
    }
}
