<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Academy extends Model
{
    use HasFactory;

    protected $filallable = [
        'name'
    ];

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_academy');
    }
}
